@extends('layouts.app')

@section('title', 'Deposits - Marine Harmony')

@section('content')
<h1>Deposit Entry</h1>

<!-- Deposit Notice Card -->
<div class="mh-deposit-notice">
    <div class="title">🏦 Official Bank Account for Deposits</div>
    <div class="reminder">Please transfer or deposit your monthly installments (BDT 5,000/- per month) to the following account:</div>
    <div class="mh-bank-card">
        <div><strong>Bank Name:</strong> Al-Arafah Islami Bank PLC</div>
        <div><strong>Branch:</strong> Principal Branch, Motijheel, Dhaka</div>
        <div><strong>Account Title:</strong> MARINE HARMONY</div>
        <div class="mh-account-number">A/C No: 0021020000000 (MSND)</div>
    </div>
</div>

<!-- Deposit Entry Form Card -->
<div class="card">
    <form action="{{ route('deposits.store') }}" method="POST" enctype="multipart/form-data" id="depositForm">
        @csrf
        <div>
            <label for="dDate">Deposit Date*</label>
            <input id="dDate" name="date" type="date" required value="{{ date('Y-m-d') }}">
        </div>

        <div>
            <label for="dMember">Member Name*</label>
            <select id="dMember" name="member" required onchange="onMemberChanged(this.value)">
                @if($isAdmin)
                    @foreach($members as $m)
                        <option value="{{ $m->name }}" {{ $m->name === $currentMemberName ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                @else
                    <option value="{{ Auth::user()->name }}" selected>{{ Auth::user()->name }}</option>
                @endif
            </select>
        </div>

        <div class="full">
            <label for="installmentMonths">Installment Month(s) Paid* (Hold Ctrl / Cmd to select multiple)</label>
            <select id="installmentMonths" name="installment_months[]" multiple size="8" class="mh-installment-select" required onchange="updateInstallmentCalculation()">
                @foreach($allMonths as $m)
                    @php
                        $label = \App\Services\FinanceService::monthLabel($m);
                        $isApproved = !empty($approvedMonths[$m]);
                        $isPending = !empty($pendingMonths[$m]);
                    @endphp
                    <option value="{{ $m }}" {{ $isApproved || $isPending ? 'disabled' : '' }}>
                        {{ $label }} {{ $isApproved ? '(Paid)' : ($isPending ? '(Approval Pending)' : '') }}
                    </option>
                @endforeach
            </select>
            <small id="installmentHint" class="hint">Select one or more unpaid months. You may pay pending or future months in advance.</small>
        </div>

        <div>
            <label for="dMethod">Payment Method*</label>
            <select id="dMethod" name="method" required onchange="updatePaymentMethodFields()">
                <option value="Bank" selected>Bank</option>
                <option value="Mobile Banking">Mobile Banking</option>
                <option value="Cash">Cash</option>
            </select>
        </div>

        <div>
            <label for="dAmount">Amount (BDT)*</label>
            <input id="dAmount" name="amount" type="number" required readonly placeholder="Calculated automatically">
        </div>

        <!-- Bank Fields -->
        <div id="bankFields" class="full grid grid-2col-gap12">
            <div>
                <label>Bank Name*</label>
                <input name="bank_name" value="Al-Arafah Islami Bank PLC" placeholder="e.g. Al-Arafah Islami Bank">
            </div>
            <div>
                <label>Branch Name*</label>
                <input name="branch" value="Principal Branch" placeholder="e.g. Principal Branch">
            </div>
            <div>
                <label>Deposit Slip No / Tx Reference</label>
                <input name="bank_ref" placeholder="e.g. SLIP-123456 or Trx ID">
            </div>
            <div>
                <label>Transaction Type</label>
                <select name="tx_type">
                    <option>Deposit Slip</option>
                    <option>Fund Transfer (NPSB/BEFTN/RTGS)</option>
                    <option>Online Banking / App</option>
                    <option>Cheque</option>
                </select>
            </div>
        </div>

        <!-- Mobile Banking Fields -->
        <div id="mobileFields" class="full grid hidden grid-2col-gap12">
            <div>
                <label>Mobile Wallet Provider*</label>
                <select name="mobile_wallet">
                    <option>bKash</option>
                    <option>Nagad</option>
                    <option>Rocket</option>
                    <option>Upay</option>
                    <option>CellFin</option>
                </select>
            </div>
            <div>
                <label>Sender Mobile Number*</label>
                <input name="mobile_number" placeholder="017XXXXXXXX">
            </div>
            <div class="full">
                <label>Transaction ID (TrxID)*</label>
                <input name="mobile_ref" placeholder="e.g. 9J87H6G5F4">
            </div>
        </div>

        <!-- Cash Fields -->
        <div id="cashFields" class="full grid hidden grid-2col-gap12">
            <div>
                <label>Cash Received By*</label>
                <input name="receiver_name" placeholder="Name of person who received cash">
            </div>
            <div>
                <label>Location / Office*</label>
                <input name="cash_location" placeholder="e.g. Dhaka Office / Meeting">
            </div>
        </div>

        <div>
            <label for="special">Special / Ad-hoc Payment?</label>
            <select id="special" name="special">
                <option value="No">No (Standard Monthly Installment)</option>
                <option value="Yes">Yes (Special / Additional Fund)</option>
            </select>
        </div>

        <div>
            <label for="remarks">Remarks / Notes</label>
            <input id="remarks" name="remarks" placeholder="Optional notes">
        </div>

        <div class="full mh-attachment">
            <label for="attachment">Attach Deposit Slip / Payment Receipt (Optional)</label>
            <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            <small>Accepts PDF, JPG, PNG, DOC, XLS up to 5 MB.</small>
        </div>

        <div class="full">
            <button type="submit" class="btn primary btn-submit-large">
                {{ $isAdmin ? 'Record & Approve Deposit' : 'Submit Deposit for Admin Approval' }}
            </button>
        </div>
    </form>
</div>

<!-- Member Deposit Status Overview -->
<div class="panel card">
    <h2>Member Deposit Status Overview</h2>
    <div class="tablewrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Paid Installments</th>
                    <th>Total Deposited</th>
                    <th>Due Months</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($memberSummaries as $ms)
                    <tr>
                        <td class="member-name-cell">
                            <strong>{{ $ms['name'] }}</strong>
                        </td>
                        <td><strong>{{ $ms['paid_months'] }}</strong> Month(s)</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($ms['total_deposited']) }}</strong></td>
                        <td>
                            @if($ms['due_count'] > 0)
                                <span class="text-red-bold">{{ $ms['due_count'] }} Month(s)</span>
                            @else
                                <span class="text-green-bold">No Dues</span>
                            @endif
                        </td>
                        <td>
                            <span class="mh-ledger-status {{ $ms['status_class'] }}">
                                {{ $ms['status_label'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Full Deposit History -->
<div class="panel card">
    <h2>Deposit Transaction Records</h2>
    <div class="tablewrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Member</th>
                    <th>Period(s)</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Receipt</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    @if($isAdmin)
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $d)
                    <tr>
                        <td>{{ $d->date ? $d->date->format('Y-m-d') : ($d->period ?: '-') }}</td>
                        <td><strong>{{ $d->member }}</strong></td>
                        <td>
                            @if($d->period)
                                <span class="pill">{{ $d->period }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $d->method ?: '-' }}</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($d->amount) }}</strong></td>
                        <td>
                            @if($d->attachment_path)
                                <a href="{{ asset($d->attachment_path) }}" target="_blank" class="btn small btn-doc-link">View Slip</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="status {{ strtolower($d->status) }}">{{ $d->status }}</span>
                        </td>
                        <td>{{ $d->remarks ?: '-' }}</td>
                        @if($isAdmin)
                            <td>
                                <form action="{{ route('deposits.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this deposit record?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger small">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="table-empty-cell">No deposit records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
function onMemberChanged(memberName) {
    fetch(`/api/members/${encodeURIComponent(memberName)}/months`)
        .then(res => res.json())
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
</script>
@endsection

