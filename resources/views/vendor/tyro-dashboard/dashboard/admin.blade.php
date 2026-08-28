@extends('tyro-dashboard::layouts.admin')

@section('title', 'Admin Dashboard - Marine Harmony')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
@php
    $depositsTotal = \App\Services\FinanceService::totalApprovedDeposits();
    $incomeTotal = \App\Services\FinanceService::totalApprovedIncome();
    $expensesTotal = \App\Services\FinanceService::totalApprovedExpenses();
    $investmentsTotal = \App\Services\FinanceService::totalApprovedInvestments();
    $bankBalance = \App\Services\FinanceService::officialBankBalance();
    $netFund = \App\Services\FinanceService::netFund();
    $pendingCount = \App\Services\FinanceService::pendingApprovalsCount();
    $pendingTotal = \App\Services\FinanceService::pendingApprovalsTotal();
    $actualBankBalance = (float) \App\Models\Setting::get('actual_bank_balance', $bankBalance);
    $isMatched = abs($actualBankBalance - $bankBalance) < 0.01;

    $recentDeposits = \App\Models\Deposit::orderByRaw("COALESCE(date, '1970-01-01') DESC")->take(6)->get();
@endphp

<!-- Hero Welcome Section -->
<div style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%), radial-gradient(circle at top right, rgba(56, 189, 248, 0.15), transparent 60%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--radius); padding: 1.75rem; margin-bottom: 2rem; color: #ffffff; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 56px; height: 56px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, #0ea5e9, #6366f1); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.5rem; color: #ffffff; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);">
                @if($user->profile_photo_path || ($user->use_gravatar ?? false))
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem;">
                    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.02em;">Welcome back, {{ $user->name }}!</h1>
                    <span style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">Administrator</span>
                </div>
                <p style="margin: 0; color: #94a3b8; font-size: 0.9375rem;">
                    Marine Harmony Financial Management & Tyro Administration Dashboard.
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('dashboard') }}" class="btn btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.2); background: rgba(255,255,255,0.05);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Financial Portal
            </a>
            @if($pendingCount > 0)
                <a href="{{ route('approval.index') }}" class="btn btn-danger" style="background: #e11d48; color: #ffffff;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approval Queue ({{ $pendingCount }})
                </a>
            @endif
            <a href="{{ route($dashboardRoute::name('users.create')) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add User
            </a>
        </div>
    </div>
</div>

<!-- Financial Intelligence KPI Grid -->
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--foreground); margin: 0;">Financial Overview & Net Liquidity</h2>
        <span style="font-size: 0.8125rem; color: var(--muted-foreground);">Live synced with Ledger</span>
    </div>

    <div class="stats-grid">
        <!-- Net Fund Card -->
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon stat-icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Net Total Fund</div>
                <div class="stat-value">{{ \App\Services\FinanceService::formatMoney($netFund) }}</div>
                <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 4px;">Bank Balance + Investments</div>
            </div>
        </div>

        <!-- Official Bank Balance -->
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon stat-icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Bank Balance</div>
                <div class="stat-value">{{ \App\Services\FinanceService::formatMoney($bankBalance) }}</div>
                <div style="font-size: 0.75rem; margin-top: 4px;">
                    @if($isMatched)
                        <span style="color: #10b981; font-weight: 600;">✓ Matched with Bank</span>
                    @else
                        <span style="color: #ef4444; font-weight: 600;">⚠ Mismatch ({{ \App\Services\FinanceService::formatMoney(abs($actualBankBalance - $bankBalance)) }})</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approved Deposits -->
        <a href="{{ route($dashboardRoute::name('resources.index'), 'deposits') }}" class="stat-card" style="text-decoration: none; color: inherit; display: block; border-left: 4px solid #6366f1;">
            <div class="stat-icon stat-icon-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Approved Deposits</div>
                <div class="stat-value">{{ \App\Services\FinanceService::formatMoney($depositsTotal) }}</div>
                <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 4px;">From Member Installments</div>
            </div>
        </a>

        <!-- Total Income -->
        <a href="{{ route($dashboardRoute::name('resources.index'), 'incomes') }}" class="stat-card" style="text-decoration: none; color: inherit; display: block; border-left: 4px solid #06b6d4;">
            <div class="stat-icon stat-icon-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Income</div>
                <div class="stat-value">{{ \App\Services\FinanceService::formatMoney($incomeTotal) }}</div>
                <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 4px;">Profit, Interest & Grants</div>
            </div>
        </a>

        <!-- Total Expenditure -->
        <a href="{{ route($dashboardRoute::name('resources.index'), 'expenses') }}" class="stat-card" style="text-decoration: none; color: inherit; display: block; border-left: 4px solid #f43f5e;">
            <div class="stat-icon stat-icon-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Expenditure</div>
                <div class="stat-value">{{ \App\Services\FinanceService::formatMoney($expensesTotal) }}</div>
                <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 4px;">Operating Costs & Charges</div>
            </div>
        </a>

        <!-- Total Investments -->
        <a href="{{ route($dashboardRoute::name('resources.index'), 'investments') }}" class="stat-card" style="text-decoration: none; color: inherit; display: block; border-left: 4px solid #8b5cf6;">
            <div class="stat-icon stat-icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Investments (FDR/DPS)</div>
                <div class="stat-value">{{ \App\Services\FinanceService::formatMoney($investmentsTotal) }}</div>
                <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 4px;">Long-term Bank Deposits</div>
            </div>
        </a>
    </div>
</div>

<!-- Dynamic CRUD Resources Quick Hub -->
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--foreground); margin: 0;">Dynamic CRUD Resources</h2>
            <p style="font-size: 0.8125rem; color: var(--muted-foreground); margin: 0;">Manage models with schema validation, search, filters & column sorting.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
        <!-- Deposits Tile -->
        <div class="card" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="badge badge-primary">{{ \App\Models\Deposit::count() }} records</span>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">Deposits Ledger</h3>
                <p style="font-size: 0.8125rem; color: var(--muted-foreground); margin: 0 0 1rem 0;">Track, review, and filter member monthly deposits.</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route($dashboardRoute::name('resources.index'), 'deposits') }}" class="btn btn-sm btn-primary" style="flex: 1; justify-content: center;">Manage</a>
                <a href="{{ route($dashboardRoute::name('resources.create'), 'deposits') }}" class="btn btn-sm btn-outline" title="Add New Deposit">+</a>
            </div>
        </div>

        <!-- Income Tile -->
        <div class="card" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(6, 182, 212, 0.1); color: #06b6d4; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="badge badge-info">{{ \App\Models\Income::count() }} records</span>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">Income Records</h3>
                <p style="font-size: 0.8125rem; color: var(--muted-foreground); margin: 0 0 1rem 0;">Earnings, bank interest, profit distributions.</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route($dashboardRoute::name('resources.index'), 'incomes') }}" class="btn btn-sm btn-primary" style="flex: 1; justify-content: center;">Manage</a>
                <a href="{{ route($dashboardRoute::name('resources.create'), 'incomes') }}" class="btn btn-sm btn-outline" title="Add New Income">+</a>
            </div>
        </div>

        <!-- Expenditures Tile -->
        <div class="card" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="badge badge-danger">{{ \App\Models\Expense::count() }} records</span>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">Expenditures</h3>
                <p style="font-size: 0.8125rem; color: var(--muted-foreground); margin: 0 0 1rem 0;">Office, administrative & operational costs.</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route($dashboardRoute::name('resources.index'), 'expenses') }}" class="btn btn-sm btn-primary" style="flex: 1; justify-content: center;">Manage</a>
                <a href="{{ route($dashboardRoute::name('resources.create'), 'expenses') }}" class="btn btn-sm btn-outline" title="Add New Expense">+</a>
            </div>
        </div>

        <!-- Investments Tile -->
        <div class="card" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="badge badge-primary">{{ \App\Models\Investment::count() }} records</span>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">Investments</h3>
                <p style="font-size: 0.8125rem; color: var(--muted-foreground); margin: 0 0 1rem 0;">Fixed deposits, maturity dates & institutions.</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route($dashboardRoute::name('resources.index'), 'investments') }}" class="btn btn-sm btn-primary" style="flex: 1; justify-content: center;">Manage</a>
                <a href="{{ route($dashboardRoute::name('resources.create'), 'investments') }}" class="btn btn-sm btn-outline" title="Add New Investment">+</a>
            </div>
        </div>
    </div>
</div>

<!-- Administration & RBAC Stats -->
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--foreground); margin: 0;">RBAC & Security Operations</h2>
        <span style="font-size: 0.8125rem; color: var(--muted-foreground);">Role-based access & system integrity</span>
    </div>

    <div class="stats-grid">
        <a href="{{ route($dashboardRoute::name('users.index')) }}" class="stat-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-icon stat-icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
            </div>
        </a>

        <a href="{{ route($dashboardRoute::name('roles.index')) }}" class="stat-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-icon stat-icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Roles & Groups</div>
                <div class="stat-value">{{ number_format($stats['total_roles'] ?? 0) }}</div>
            </div>
        </a>

        <a href="{{ route($dashboardRoute::name('privileges.index')) }}" class="stat-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-icon stat-icon-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Privileges</div>
                <div class="stat-value">{{ number_format($stats['total_privileges'] ?? 0) }}</div>
            </div>
        </a>

        <a href="{{ route($dashboardRoute::name('health.index')) }}" class="stat-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-icon stat-icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">System Health</div>
                <div class="stat-value" style="font-size: 1.25rem; color: #10b981;">Operational</div>
            </div>
        </a>
    </div>
</div>

<!-- Two Column Split: Recent Transactions & Recent Users -->
<div class="grid-2">
    <!-- Recent Transactions Ledger -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 class="card-title" style="font-size: 1.0625rem;">Recent Deposit Transactions</h3>
                <p class="card-description" style="font-size: 0.8125rem;">Latest submissions from members</p>
            </div>
            <a href="{{ route('deposits.index') }}" class="btn btn-sm btn-ghost">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDeposits as $tx)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 0.875rem;">{{ $tx->member ?: ($tx->submitted_by ?: 'Member') }}</div>
                                <div style="font-size: 0.75rem; color: var(--muted-foreground);">{{ $tx->date ? $tx->date->format('M d, Y') : ($tx->period ?: '-') }} • {{ $tx->method ?: 'Bank' }}</div>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #10b981;">{{ \App\Services\FinanceService::formatMoney($tx->amount) }}</span>
                            </td>
                            <td>
                                @if($tx->status === 'Approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($tx->status === 'Rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--muted-foreground); padding: 1.5rem;">No recent deposits found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Users & Roles Distribution -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 class="card-title" style="font-size: 1.0625rem;">Recent Users & Roles</h3>
                <p class="card-description" style="font-size: 0.8125rem;">Registered members in system</p>
            </div>
            <a href="{{ route($dashboardRoute::name('users.index')) }}" class="btn btn-sm btn-ghost">Manage Users</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if(isset($stats['recent_users']) && $stats['recent_users']->count())
            <div class="table-container">
                <table class="table">
                    <tbody>
                        @foreach($stats['recent_users'] as $recentUser)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-cell-avatar" style="{{ ($recentUser->profile_photo_path || $recentUser->use_gravatar) ? 'background: none; padding: 0;' : '' }}">
                                        @if($recentUser->profile_photo_path || ($recentUser->use_gravatar && $recentUser->email))
                                            <img src="{{ $recentUser->profile_photo_url }}" alt="{{ $recentUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                        @else
                                            {{ strtoupper(substr($recentUser->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="user-cell-info">
                                        <div class="user-cell-name" style="font-size: 0.875rem; font-weight: 600;">{{ $recentUser->name }}</div>
                                        <div class="user-cell-email" style="font-size: 0.75rem;">{{ $recentUser->email ?: $recentUser->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                @if(method_exists($recentUser, 'isAdmin') && $recentUser->isAdmin())
                                    <span class="badge badge-primary">Admin</span>
                                @else
                                    <span class="badge badge-secondary">Member</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <p class="empty-state-description">No users found.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

