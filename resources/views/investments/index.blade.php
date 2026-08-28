@extends('layouts.app')

@section('title', 'Investments - Marine Harmony')

@section('content')
<h1>Investment Entry</h1>

<div class="card">
    <form action="{{ route('investments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="lDate">Date*</label>
            <input id="lDate" name="date" type="date" required value="{{ date('Y-m-d') }}">
        </div>

        <div>
            <label for="lCategory">Institution / Investment*</label>
            <input id="lCategory" name="institution" placeholder="e.g. Al-Arafah Islami Bank PLC" required>
        </div>

        <div>
            <label for="lPurpose">Purpose / Instrument*</label>
            <input id="lPurpose" name="purpose" placeholder="e.g. MTDR / FDR 3-Month Fund" required>
        </div>

        <div>
            <label for="lAmount">Amount (BDT)*</label>
            <input id="lAmount" name="amount" type="number" min="0.01" step="0.01" placeholder="0.00" required>
        </div>

        <div class="full mh-ledger-details">
            <label for="lDetails">Investment Details & Terms*</label>
            <textarea id="lDetails" name="details" rows="3" required placeholder="FDR No, account title, interest/profit rate, branch details."></textarea>
        </div>

        <div>
            <label for="termMonths">Term (Months)</label>
            <input id="termMonths" name="term_months" type="number" min="1" placeholder="e.g. 3">
        </div>

        <div>
            <label for="maturityDate">Maturity Date</label>
            <input id="maturityDate" name="maturity_date" type="date">
        </div>

        <div class="full checkbox-row">
            <input id="autoRenew" name="auto_renew" type="checkbox" value="1" class="checkbox-input" checked>
            <label for="autoRenew" class="checkbox-label">Auto-Renew at Maturity (Principal & Profit Rollover)</label>
        </div>

        <div class="full">
            <label for="lRef">Reference / FDR Number</label>
            <input id="lRef" name="ref" placeholder="e.g. AIB-MTDR-1000K-01">
        </div>

        <div class="full mh-attachment">
            <label for="invAttachment">Attach Supporting Document / Certificate (Optional)</label>
            <input id="invAttachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            <small>Attach FDR certificate, agreement, voucher or bank confirmation.</small>
        </div>

        <div class="full">
            <button type="submit" class="btn primary">
                {{ $isAdmin ? 'Record & Approve Investment' : 'Submit for Admin Approval' }}
            </button>
        </div>
    </form>
</div>

<div class="panel card">
    <div class="notice">
        {{ $isAdmin ? 'Admin view: all investment portfolios and MTDR records are shown below.' : 'View-only: approved investment records are shown below.' }}
    </div>

    <div class="tablewrap mt-12">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Submitted By</th>
                    <th>Institution</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                    <th>Details & Term</th>
                    <th>Document</th>
                    <th>Status</th>
                    <th>Reference</th>
                    @if($isAdmin)
                        <th>Admin</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($investments as $inv)
                    <tr>
                        <td>{{ $inv->date ? $inv->date->format('Y-m-d') : '-' }}</td>
                        <td><strong>{{ $inv->submitted_by ?: '-' }}</strong></td>
                        <td>{{ $inv->institution ?: '-' }}</td>
                        <td>{{ $inv->purpose ?: '-' }}</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($inv->amount) }}</strong></td>
                        <td>
                            {{ $inv->details ?: '-' }}
                            @if($inv->term_months || $inv->maturity_date)
                                <br>
                                <small class="hint">
                                    <strong>Term:</strong> {{ $inv->term_months ?: 3 }} months |
                                    <strong>Maturity:</strong> {{ $inv->maturity_date ? $inv->maturity_date->format('Y-m-d') : '-' }}
                                    @if($inv->auto_renew)
                                        | <span class="text-green-bold">Auto-Renew Active</span>
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($inv->attachment_path)
                                <a href="{{ asset($inv->attachment_path) }}" target="_blank" class="btn small btn-doc-link">View Doc</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="status {{ strtolower($inv->status) }}">{{ $inv->status }}</span>
                        </td>
                        <td>{{ $inv->ref ?: '-' }}</td>
                        @if($isAdmin)
                            <td>
                                <form action="{{ route('investments.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('Delete this investment record?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger small">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 10 : 9 }}" class="table-empty-cell">No investment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

