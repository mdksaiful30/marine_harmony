@extends('layouts.app')

@section('title', 'Reports & Financial Statements - Marine Harmony')

@section('content')
<div class="mh-reports-shell">
    <div class="mh-reports-head">
        <h1 class="mh-reports-title">Financial Reports</h1>
        <div class="mh-report-export">
            @if($type === 'Ledger')
                <button type="button" title="Export Ledger as PDF" class="btn primary" onclick="exportLedgerPDF('mhLedgerCapture', 'Marine_Harmony_Ledger.pdf')">
                    📄 PDF
                </button>
                <button type="button" title="Export Ledger as Image" class="btn" onclick="exportLedgerJPG('mhLedgerCapture', 'Marine_Harmony_Ledger.jpg')">
                    🖼️ JPG
                </button>
            @else
                <button type="button" title="Export Table as Excel" class="btn success" onclick="exportTableToExcel('mhRTable', 'Marine_Harmony_Transactions.xlsx')">
                    📊 Excel
                </button>
            @endif
        </div>
    </div>

    <!-- Report Navigation Tabs -->
    <div class="mh-report-tabs" aria-label="Report types">
        <a href="{{ route('reports.index', ['type' => 'Ledger', 'member' => $selectedMember, 'from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search]) }}"
           class="mh-report-tab {{ $type === 'Ledger' ? 'active' : '' }}">
            <span class="ico">📑</span>Member Ledger
        </a>
        <a href="{{ route('reports.index', ['type' => 'All', 'member' => $selectedMember, 'from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search]) }}"
           class="mh-report-tab {{ $type === 'All' ? 'active' : '' }}">
            <span class="ico">📋</span>All Transactions
        </a>
        <a href="{{ route('reports.index', ['type' => 'Deposit', 'member' => $selectedMember, 'from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search]) }}"
           class="mh-report-tab {{ $type === 'Deposit' ? 'active' : '' }}">
            <span class="ico">📥</span>Deposits
        </a>
        <a href="{{ route('reports.index', ['type' => 'Income', 'member' => $selectedMember, 'from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search]) }}"
           class="mh-report-tab {{ $type === 'Income' ? 'active' : '' }}">
            <span class="ico">📈</span>Income
        </a>
        <a href="{{ route('reports.index', ['type' => 'Expense', 'member' => $selectedMember, 'from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search]) }}"
           class="mh-report-tab {{ $type === 'Expense' ? 'active' : '' }}">
            <span class="ico">📉</span>Expenditure
        </a>
        <a href="{{ route('reports.index', ['type' => 'Investment', 'member' => $selectedMember, 'from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search]) }}"
           class="mh-report-tab {{ $type === 'Investment' ? 'active' : '' }}">
            <span class="ico">🏦</span>Investments
        </a>
    </div>

    <!-- Filter & Search Card -->
    <div class="mh-report-filter-card">
        <div class="mh-filter-title">🔍 Filter &amp; Search</div>
        <form action="{{ route('reports.index') }}" method="GET" class="mh-filter-grid">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="mh-filter-field">
                <label for="repMember">Member</label>
                <select id="repMember" name="member">
                    <option value="">All Members</option>
                    @foreach($members as $m)
                        <option value="{{ $m->name }}" {{ $selectedMember === $m->name ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mh-filter-field">
                <label for="repFrom">From Date</label>
                <input id="repFrom" name="from_date" type="date" value="{{ $fromDate }}">
            </div>

            <div class="mh-filter-field">
                <label for="repTo">To Date</label>
                <input id="repTo" name="to_date" type="date" value="{{ $toDate }}">
            </div>

            <div class="mh-filter-field full">
                <label for="repSearch">Search by member name, purpose, source or description</label>
                <div class="search-input-group">
                    <input id="repSearch" name="search" placeholder="Search keywords..." value="{{ $search }}" class="flex-1">
                    <button type="submit" class="btn primary min-w-110">Search</button>
                    @if($selectedMember || $fromDate || $toDate || $search)
                        <a href="{{ route('reports.index', ['type' => $type]) }}" class="btn btn-reset">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Report Output Container -->
    <div id="mhReportOutput" class="mt-16">
        @if($type === 'Ledger')
            @if($ledgerData)
                <!-- Individual Member Ledger Card -->
                <div id="mhLedgerCapture" class="card ledger-capture-card">
                    <div class="ledger-header">
                        <div class="ledger-brand">
                            <img src="{{ asset('images/logo.jpg') }}" alt="Marine Harmony" class="ledger-logo">
                            <div>
                                <h2 class="ledger-title">MARINE HARMONY</h2>
                                <p class="ledger-subtitle">Member Financial Statement &amp; Deposit Ledger</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="ledger-statement-date">Statement Date: <strong>{{ date('d M Y') }}</strong></div>
                            <div class="mt-4">
                                <span class="mh-ledger-status {{ $ledgerData['summary']['status_class'] }}">
                                    {{ $ledgerData['summary']['status_label'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Member Info & Metrics -->
                    <div class="ledger-member-bar">
                        @if($ledgerData['member'] && $ledgerData['member']->avatar)
                            <img class="member-avatar ledger-avatar" src="{{ asset($ledgerData['member']->avatar) }}" alt="{{ $ledgerData['memberName'] }}">
                        @else
                            <span class="member-avatar member-avatar-fallback ledger-avatar">{{ substr($ledgerData['memberName'], 0, 3) }}</span>
                        @endif
                        <div class="ledger-member-info">
                            <h3 class="ledger-member-name">{{ $ledgerData['memberName'] }}</h3>
                            <p class="ledger-member-sub">Official Project Member</p>
                        </div>
                        <div class="ledger-stat-cards">
                            <div class="ledger-stat-box">
                                <div class="ledger-stat-label">Paid Months</div>
                                <div class="ledger-stat-val-navy">{{ $ledgerData['summary']['paid_months'] }}</div>
                            </div>
                            <div class="ledger-stat-box">
                                <div class="ledger-stat-label">Total Deposited</div>
                                <div class="ledger-stat-val-green">{{ \App\Services\FinanceService::formatMoney($ledgerData['summary']['total_deposited']) }}</div>
                            </div>
                            <div class="ledger-stat-box">
                                <div class="ledger-stat-label">Due Count</div>
                                <div class="{{ $ledgerData['summary']['due_count'] > 0 ? 'ledger-stat-val-red' : 'ledger-stat-val-green' }}">
                                    {{ $ledgerData['summary']['due_count'] }} mo
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Breakdown Table -->
                    <div class="tablewrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Month Period</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Payment Method</th>
                                    <th>Amount (BDT)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ledgerData['breakdown'] as $mb)
                                    <tr>
                                        <td><strong>{{ $mb['label'] }}</strong> ({{ $mb['period'] }})</td>
                                        <td>
                                            @if($mb['status'] === 'Paid')
                                                <span class="pill pill-paid">✓ Paid</span>
                                            @else
                                                <span class="pill pill-due">⚠ Due</span>
                                            @endif
                                        </td>
                                        <td>{{ $mb['date'] }}</td>
                                        <td>{{ $mb['method'] }}</td>
                                        <td><strong>{{ $mb['amount'] > 0 ? \App\Services\FinanceService::formatMoney($mb['amount']) : '-' }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- All Members Compact Ledger View -->
                <div id="mhLedgerCapture" class="card ledger-all-card">
                    <div class="ledger-all-header">
                        <div>
                            <h2 class="ledger-all-title">⚓ MARINE HARMONY — All Members Deposit Ledger</h2>
                            <p class="ledger-all-subtitle">Cumulative Summary Statement • Generated on {{ date('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="tablewrap">
                        <table id="mhRTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Member Name</th>
                                    <th>Paid Installments</th>
                                    <th>Total Deposited</th>
                                    <th>Dues</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allMembersLedger as $idx => $mItem)
                                    @php
                                        $u = $mItem['user'];
                                        $s = $mItem['summary'];
                                    @endphp
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="member-name-cell">
                                            @if($u->avatar)
                                                <img class="member-avatar member-avatar-compact" src="{{ asset($u->avatar) }}" alt="{{ $u->name }}">
                                            @else
                                                <span class="member-avatar member-avatar-fallback member-avatar-compact">{{ $u->initials }}</span>
                                            @endif
                                            <strong>{{ $u->name }}</strong>
                                        </td>
                                        <td><strong>{{ $s['paid_months'] }}</strong> Month(s)</td>
                                        <td><strong>{{ \App\Services\FinanceService::formatMoney($s['total_deposited']) }}</strong></td>
                                        <td>
                                            @if($s['due_count'] > 0)
                                                <strong class="text-red">{{ $s['due_count'] }} Month(s)</strong>
                                            @else
                                                <span class="text-green-bold">0 Due</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="mh-ledger-status {{ $s['status_class'] }}">
                                                {{ $s['status_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('reports.index', ['type' => 'Ledger', 'member' => $u->name]) }}" class="btn small primary">
                                                Statement
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <!-- Standard Transaction Register Table -->
            <div class="card card-padding-20">
                <div class="mh-report-summary">
                    {{ $transactions->count() }} record(s) found |
                    Total: <strong>{{ \App\Services\FinanceService::formatMoney($transactions->sum('amount')) }}</strong>
                </div>

                <div class="tablewrap mt-12">
                    <table id="mhRTable">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Member / Submitter</th>
                                <th>Source / Method</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Details</th>
                                <th>Historical</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td><strong>{{ $t['type'] }}</strong></td>
                                    <td>{{ $t['date'] }}</td>
                                    <td><strong>{{ $t['member'] }}</strong></td>
                                    <td>{{ $t['source'] }}</td>
                                    <td>{{ $t['category'] }}</td>
                                    <td><strong>{{ \App\Services\FinanceService::formatMoney($t['amount']) }}</strong></td>
                                    <td>{{ $t['details'] }}</td>
                                    <td>{{ $t['historical'] }}</td>
                                    <td>
                                        <span class="status {{ strtolower($t['status']) }}">{{ $t['status'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="table-empty-cell-large">
                                        No records match your selected filters. Try adjusting your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

