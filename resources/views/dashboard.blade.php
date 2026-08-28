@extends('layouts.app')

@section('title', 'Dashboard - Marine Harmony')

@section('content')
<h1>Dashboard</h1>

<!-- Metrics Cards Grid -->
<div class="grid">
    <div class="card metric">
        <div class="label">Approved Deposits</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalDeposits) }}</div>
    </div>
    <div class="card metric">
        <div class="label">Total Income</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalIncome) }}</div>
    </div>
    <div class="card metric">
        <div class="label">Total Expenditure</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalExpenses) }}</div>
    </div>
    <div class="card metric">
        <div class="label">Total Investments (MTDR / FDR)</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalInvestments) }}</div>
    </div>
    <div class="card metric">
        <div class="label">Official Bank Balance</div>
        <div class="value" id="calcBankBalance" data-amount="{{ $officialBankBalance }}">
            {{ \App\Services\FinanceService::formatMoney($officialBankBalance) }}
        </div>
        <small class="hint">Deposits + Income − Expenditure − Investments</small>
    </div>
    <div class="card metric">
        <div class="label">Net Total Fund</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($netFund) }}</div>
        <small class="hint">Bank Balance + Investments</small>
    </div>
    @if($isAdmin)
        <div class="card metric">
            <div class="label">Pending Admin Approvals</div>
            <div class="value text-amber">{{ \App\Services\FinanceService::formatMoney($pendingTotal) }}</div>
            <small class="hint">{{ $pendingCount }} item(s) awaiting approval</small>
        </div>
    @endif
</div>

@if($isAdmin)
    {{-- Admin Bank Balance Verification Panel --}}
    <div class="panel card">
        <h2>Admin Bank Balance Verification</h2>

        <div id="reconcileAlertBox" class="{{ $isMatched ? 'mh-balance-ok' : 'mh-balance-alert' }}">
            @if($isMatched)
                <strong>✓ BANK BALANCE MATCHED</strong><br>
                Actual bank balance and calculated balance both equal <strong>{{ \App\Services\FinanceService::formatMoney($officialBankBalance) }}</strong>.
            @else
                <strong>⚠ BANK BALANCE MISMATCH</strong><br>
                Calculated balance: <strong>{{ \App\Services\FinanceService::formatMoney($officialBankBalance) }}</strong> &nbsp; | &nbsp;
                Actual bank balance: <strong>{{ \App\Services\FinanceService::formatMoney($actualBankBalance) }}</strong><br>
                Difference: <strong>{{ \App\Services\FinanceService::formatMoney($diff) }}</strong><br>
                <span>Please reconcile deposits, income, expenses and investments with the bank statement.</span>
            @endif
        </div>

        <form action="{{ route('dashboard.reconcile') }}" method="POST" class="inline-form mt-14">
            @csrf
            <div>
                <label for="actualBankBalanceInput">Actual Bank Balance (from Bank Statement / Online Portal):</label>
                <input id="actualBankBalanceInput" name="actual_bank_balance" type="number" step="0.01" value="{{ $actualBankBalance }}" required oninput="calculateBankReconciliation()">
            </div>
            <div>
                <button type="submit" class="btn primary">Save Actual Bank Balance</button>
            </div>
        </form>
    </div>
@endif

<!-- Recent Transactions Panel -->
<div class="panel card">
    <h2>Recent Transactions</h2>
    <div class="tablewrap">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Member / Submitter</th>
                    <th>Method / Source</th>
                    <th>Amount</th>
                    <th>Details</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDeposits as $tx)
                    <tr>
                        <td><strong>Deposit</strong></td>
                        <td>{{ $tx->date ? $tx->date->format('Y-m-d') : ($tx->period ?: '-') }}</td>
                        <td>{{ $tx->member ?: ($tx->submitted_by ?: '-') }}</td>
                        <td>{{ $tx->method ?: '-' }}</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($tx->amount) }}</strong></td>
                        <td>
                            @if($tx->period)
                                <span class="pill">Period: {{ $tx->period }}</span>
                            @endif
                            {{ $tx->remarks ?: '-' }}
                        </td>
                        <td>
                            <span class="status {{ strtolower($tx->status) }}">
                                {{ $tx->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-empty-cell">No recent transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

