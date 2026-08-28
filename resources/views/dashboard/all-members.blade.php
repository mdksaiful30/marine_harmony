@extends('tyro-dashboard::layouts.app')

@section('title', 'All Members')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>All Members</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <h1 class="page-title">All Marine Harmony Members</h1>
            <p class="page-description" style="font-size: 0.9375rem; color: var(--muted-foreground);">
                Directory of registered project members and real-time financial standing.
            </p>
        </div>
        <div>
            <a href="{{ route('members.index') }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 6px 14px; text-decoration: none; border-radius: 8px;">
                Full Members Directory & Table →
            </a>
        </div>
    </div>
</div>

@php
    $members = \App\Models\User::orderBy('id')->get();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
    @foreach($members as $m)
        @php
            $summary = \App\Services\FinanceService::getMemberLedgerSummary($m->name);
        @endphp
        <div class="card" style="border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; background: var(--card); transition: all 0.2s;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                @if($m->avatar)
                    <img src="{{ asset($m->avatar) }}" alt="{{ $m->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);">
                @else
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #1683c7, #0aa6a6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                        {{ $m->initials }}
                    </div>
                @endif
                <div style="flex: 1; min-width: 0;">
                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $m->name }}
                    </h4>
                    <div style="font-size: 12px; color: var(--muted-foreground); margin-top: 2px;">
                        <span class="badge" style="background: {{ $m->isAdmin() ? '#fee2e2' : '#e0f2fe' }}; color: {{ $m->isAdmin() ? '#991b1b' : '#0369a1' }}; font-size: 11px; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                            {{ $m->isAdmin() ? 'Admin' : 'Member' }}
                        </span>
                        @if($m->username)
                            <span style="margin-left: 4px;">@ {{ $m->username }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="padding: 10px; background: var(--muted); border-radius: 8px; font-size: 12px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: var(--muted-foreground);">Paid Installments:</span>
                    <strong style="color: var(--foreground);">{{ $summary['paid_months'] }} Months</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: var(--muted-foreground);">Total Deposited:</span>
                    <strong style="color: var(--foreground);">{{ \App\Services\FinanceService::formatMoney($summary['total_deposited']) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--muted-foreground);">Status:</span>
                    <strong style="color: {{ $summary['due_count'] > 0 ? '#ef4444' : '#10b981' }};">
                        {{ $summary['status_label'] }}
                    </strong>
                </div>
            </div>

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('members.show', $m->id) }}" class="btn btn-sm" style="flex: 1; text-align: center; background: #1683c7; color: #fff; border-radius: 6px; padding: 6px 10px; font-weight: 600; text-decoration: none; font-size: 12px;">
                    View Profile
                </a>
                <a href="{{ route('reports.index', ['type' => 'Ledger', 'member' => $m->name]) }}" class="btn btn-sm" style="background: var(--muted); color: var(--foreground); border: 1px solid var(--border); border-radius: 6px; padding: 6px 10px; font-weight: 600; text-decoration: none; font-size: 12px;">
                    Ledger
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection
