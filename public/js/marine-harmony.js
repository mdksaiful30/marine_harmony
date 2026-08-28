/**
 * Marine Harmony Main Client Scripts
 */

// Monthly Installment Constant
window.MONTHLY_INSTALLMENT = window.MONTHLY_INSTALLMENT || 5000;

// Format money helper
function formatMoney(amount) {
    return 'BDT ' + Number(amount || 0).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Deposits: Calculate installment total
function updateInstallmentCalculation() {
    const select = document.getElementById('installmentMonths');
    const amountInput = document.getElementById('dAmount');
    const hint = document.getElementById('installmentHint');
    if (!select || !amountInput) return;

    const selectedOptions = Array.from(select.selectedOptions).filter(opt => !opt.disabled);
    const count = selectedOptions.length;
    const total = count * (window.MONTHLY_INSTALLMENT || 5000);

    amountInput.value = total > 0 ? total : '';
    if (hint) {
        if (count > 0) {
            hint.innerHTML = `Selected <strong>${count}</strong> month(s). Total: <strong>${formatMoney(total)}</strong>.`;
        } else {
            hint.textContent = 'Select one or more unpaid months. You may pay pending or future months in advance.';
        }
    }
}

// Deposits: Switch payment method fields
function updatePaymentMethodFields() {
    const methodSelect = document.getElementById('dMethod');
    if (!methodSelect) return;
    const method = methodSelect.value;

    const bankFields = document.getElementById('bankFields');
    const mobileFields = document.getElementById('mobileFields');
    const cashFields = document.getElementById('cashFields');

    if (bankFields) bankFields.classList.toggle('hidden', method !== 'Bank');
    if (mobileFields) mobileFields.classList.toggle('hidden', method !== 'Mobile Banking');
    if (cashFields) cashFields.classList.toggle('hidden', method !== 'Cash');
}

// Deposits: Fetch Member Months dynamically
function onMemberChanged(memberName) {
    if (!memberName) return;
    fetch(`/api/members/${encodeURIComponent(memberName)}/months`)
        .then(res => {
            if (!res.ok) throw new Error('Network error fetching months');
            return res.json();
        })
        .then(data => {
            const select = document.getElementById('installmentMonths');
            if (!select || !data.options) return;
            select.innerHTML = '';
            data.options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.period;
                option.text = opt.text;
                if (opt.disabled) option.disabled = true;
                select.appendChild(option);
            });
            updateInstallmentCalculation();
        })
        .catch(err => console.error('Error fetching member months:', err));
}

// Dashboard: Bank Reconciliation calculation
function calculateBankReconciliation() {
    const calcEl = document.getElementById('calcBankBalance');
    const input = document.getElementById('actualBankBalanceInput');
    const box = document.getElementById('reconcileAlertBox');
    if (!calcEl || !input || !box) return;

    const official = parseFloat(calcEl.getAttribute('data-amount') || 0);
    const actual = parseFloat(input.value || 0);
    const diff = official - actual;
    const isMatched = Math.abs(diff) < 0.01;

    box.className = isMatched ? 'mh-balance-ok' : 'mh-balance-alert';
    if (isMatched) {
        box.innerHTML = `<strong>✓ BANK BALANCE MATCHED</strong><br>Actual bank balance and calculated balance both equal <strong>${formatMoney(official)}</strong>.`;
    } else {
        box.innerHTML = `<strong>⚠ BANK BALANCE MISMATCH</strong><br>Calculated balance: <strong>${formatMoney(official)}</strong> &nbsp; | &nbsp; Actual bank balance: <strong>${formatMoney(actual)}</strong><br>Difference: <strong>${formatMoney(diff)}</strong><br><span>Please reconcile deposits, income, expenses and investments with the bank statement.</span>`;
    }
}

// Reports: Export Table to Excel
function exportTableToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) {
        alert('No table found to export.');
        return;
    }
    if (typeof XLSX === 'undefined') {
        alert('Excel export library is loading or unavailable.');
        return;
    }
    const wb = XLSX.utils.table_to_book(table, { sheet: 'Marine Harmony' });
    XLSX.writeFile(wb, filename || 'Marine_Harmony_Report.xlsx');
}

// Reports: Export Ledger as PDF
function exportLedgerPDF(elementId, filename) {
    const el = document.getElementById(elementId);
    if (!el) {
        alert('No ledger content found to export.');
        return;
    }
    if (!window.html2canvas || !window.jspdf || !window.jspdf.jsPDF) {
        window.print();
        return;
    }
    html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(canvas => {
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
        pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(filename || 'Marine_Harmony_Ledger.pdf');
    }).catch(err => {
        console.error('PDF export failed:', err);
        window.print();
    });
}

// Reports: Export Ledger as JPG
function exportLedgerJPG(elementId, filename) {
    const el = document.getElementById(elementId);
    if (!el) {
        alert('No ledger content found to export.');
        return;
    }
    if (!window.html2canvas) {
        alert('Image export library unavailable.');
        return;
    }
    html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(canvas => {
        const link = document.createElement('a');
        link.download = filename || 'Marine_Harmony_Ledger.jpg';
        link.href = canvas.toDataURL('image/jpeg', 0.95);
        link.click();
    }).catch(err => {
        console.error('JPG export failed:', err);
        alert('Image export failed.');
    });
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentMethodFields();
    updateInstallmentCalculation();
});
