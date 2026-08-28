@extends('layouts.app')

@section('title', 'Members - Marine Harmony')

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 10px;">
    <div>
        <h1 style="margin: 0;">Marine Harmony Members Directory</h1>
        <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">Click on any member to view their comprehensive financial profile and installment ledger.</p>
    </div>
    <div>
        <a href="{{ route('reports.index', ['type' => 'Ledger']) }}" class="btn small" style="background: #e2e8f0; color: #1e293b; font-weight: 600; text-decoration: none; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            All Members Ledger
        </a>
    </div>
</div>

<!-- Member Photo Grid -->
<div class="panel card mh-member-photo-panel">
    <h2>Registered Members & Profiles ({{ count($memberSummaries) }})</h2>
    <div class="mh-member-photo-grid">
        @foreach($memberSummaries as $item)
            @php
                $u = $item['user'];
                $s = $item['summary'];
            @endphp
            <a href="{{ route('members.show', $u->id) }}" class="mh-member-photo-card mh-member-clickable" title="View {{ $u->name }}'s Profile">
                @if($u->avatar)
                    <img class="member-avatar" src="{{ asset($u->avatar) }}" alt="{{ $u->name }}">
                @else
                    <span class="member-avatar member-avatar-fallback">{{ $u->initials }}</span>
                @endif
                <div class="flex-1-min0">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                        <strong style="color: var(--navy); font-size: 14px;">{{ $u->name }}</strong>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: #1683c7; opacity: 0.7;">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </div>
                    <small style="display: block; margin-top: 2px;">
                        <span class="pill pill-role-tag">{{ $u->isAdmin() ? 'Project Admin' : 'Member' }}</span>
                    </small>
                    <div class="member-stat-box">
                        <div>Paid: <strong>{{ $s['paid_months'] }}</strong> mo ({{ \App\Services\FinanceService::formatMoney($s['total_deposited']) }})</div>
                        <div class="mt-2" style="display: flex; align-items: center; justify-content: space-between;">
                            <span class="mh-ledger-status {{ $s['status_class'] }} pill-status-tag">
                                {{ $s['status_label'] }}
                            </span>
                            <span style="font-size: 11px; color: #1683c7; font-weight: 700;">View Profile →</span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>

<!-- Detailed Members Table -->
<div class="panel card">
    <h2>Member Financial Ledger Status</h2>
    <div class="tablewrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member Name</th>
                    <th>Role</th>
                    <th>Installments Paid</th>
                    <th>Total Deposited</th>
                    <th>Current Dues</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($memberSummaries as $index => $item)
                    @php
                        $u = $item['user'];
                        $s = $item['summary'];
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="member-name-cell">
                            <a href="{{ route('members.show', $u->id) }}" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                                @if($u->avatar)
                                    <img class="member-avatar member-avatar-small" src="{{ asset($u->avatar) }}" alt="{{ $u->name }}">
                                @else
                                    <span class="member-avatar member-avatar-fallback member-avatar-small">{{ $u->initials }}</span>
                                @endif
                                <div>
                                    <strong style="color: #073b66;">{{ $u->name }}</strong>
                                    @if($u->email)<div style="font-size: 11px; color: #64748b;">{{ $u->email }}</div>@endif
                                </div>
                            </a>
                        </td>
                        <td>
                            <span class="pill">{{ $u->isAdmin() ? 'Admin' : 'Member' }}</span>
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
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('members.show', $u->id) }}" class="btn small" style="background: #e0f2fe; color: #0369a1; font-weight: 700; text-decoration: none; border-radius: 6px; padding: 5px 10px;">
                                    Profile
                                </a>
                                <a href="{{ route('reports.index', ['type' => 'Ledger', 'member' => $u->name]) }}" class="btn small primary" style="text-decoration: none; border-radius: 6px; padding: 5px 10px;">
                                    Ledger
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
.mh-member-clickable {
    text-decoration: none;
    color: inherit;
    transition: all 0.22s ease-in-out;
    cursor: pointer;
    position: relative;
}
.mh-member-clickable:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(7, 59, 102, 0.14);
    border-color: #1683c7 !important;
}
.mh-member-clickable:hover strong {
    color: #1683c7;
}
</style>
@endsection


