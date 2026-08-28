@extends('layouts.app')

@section('title', 'Dashboard - Marine Harmony')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="margin: 0; font-size: 1.75rem; font-weight: 800; color: var(--navy);">Financial Dashboard</h1>
        <p style="margin: 4px 0 0 0; color: var(--muted); font-size: 0.9375rem;">Real-time balance, member contributions, and liquid funds summary.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('deposits.index') }}" class="btn primary" style="text-decoration: none;">+ Submit Deposit</a>
        <a href="{{ route('income.index') }}" class="btn" style="text-decoration: none;">+ Add Income</a>
        <a href="{{ route('expenses.index') }}" class="btn" style="text-decoration: none;">+ Add Expense</a>
        <a href="{{ route('reports.index') }}" class="btn" style="text-decoration: none;">📊 Reports</a>
    </div>
</div>

<!-- Metrics Cards Grid -->
<div class="grid">
    <div class="card metric" style="border-top: 4px solid #1683c7;">
        <div class="label">Net Total Fund</div>
        <div class="value" style="color: #073b66;">{{ \App\Services\FinanceService::formatMoney($netFund) }}</div>
        <small class="hint">Bank Balance + Investments</small>
    </div>
    <div class="card metric" style="border-top: 4px solid #10b981;">
        <div class="label">Official Bank Balance</div>
        <div class="value" id="calcBankBalance" data-amount="{{ $officialBankBalance }}" style="color: #15803d;">
            {{ \App\Services\FinanceService::formatMoney($officialBankBalance) }}
        </div>
        <small class="hint">Deposits + Income − Expense − Investments</small>
    </div>
    <div class="card metric" style="border-top: 4px solid #6366f1;">
        <div class="label">Approved Deposits</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalDeposits) }}</div>
        <small class="hint">Member regular installments</small>
    </div>
    <div class="card metric" style="border-top: 4px solid #06b6d4;">
        <div class="label">Total Income</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalIncome) }}</div>
        <small class="hint">Earnings, interest & grants</small>
    </div>
    <div class="card metric" style="border-top: 4px solid #f43f5e;">
        <div class="label">Total Expenditure</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalExpenses) }}</div>
        <small class="hint">Administrative & operations</small>
    </div>
    <div class="card metric" style="border-top: 4px solid #8b5cf6;">
        <div class="label">Total Investments (MTDR/FDR)</div>
        <div class="value">{{ \App\Services\FinanceService::formatMoney($totalInvestments) }}</div>
        <small class="hint">Fixed deposit receipts</small>
    </div>
    @if($isAdmin)
        <div class="card metric" style="border-top: 4px solid #f59e0b;">
            <div class="label">Pending Admin Approvals</div>
            <div class="value text-amber">{{ \App\Services\FinanceService::formatMoney($pendingTotal) }}</div>
            <small class="hint">
                <a href="{{ route('approval.index') }}" style="color: #b54708; font-weight: 700; text-decoration: underline;">
                    {{ $pendingCount }} item(s) awaiting approval →
                </a>
            </small>
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

