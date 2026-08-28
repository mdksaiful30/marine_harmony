@extends('layouts.app')

@section('title', 'Members - Marine Harmony')

@section('content')
<h1>Marine Harmony Members Directory</h1>

<!-- Member Photo Grid -->
<div class="panel card mh-member-photo-panel">
    <h2>Registered Members & Profiles</h2>
    <div class="mh-member-photo-grid">
        @foreach($memberSummaries as $item)
            @php
                $u = $item['user'];
                $s = $item['summary'];
            @endphp
            <div class="mh-member-photo-card">
                @if($u->avatar)
                    <img class="member-avatar" src="{{ asset($u->avatar) }}" alt="{{ $u->name }}">
                @else
                    <span class="member-avatar member-avatar-fallback">{{ $u->initials }}</span>
                @endif
                <div class="flex-1-min0">
                    <strong>{{ $u->name }}</strong>
                    <small>
                        <span class="pill pill-role-tag">{{ $u->isAdmin() ? 'Project Admin' : 'Member' }}</span>
                    </small>
                    <div class="member-stat-box">
                        <div>Paid: <strong>{{ $s['paid_months'] }}</strong> mo ({{ \App\Services\FinanceService::formatMoney($s['total_deposited']) }})</div>
                        <div class="mt-2">
                            <span class="mh-ledger-status {{ $s['status_class'] }} pill-status-tag">
                                {{ $s['status_label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
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
                    <th>Ledger Statement</th>
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
                            @if($u->avatar)
                                <img class="member-avatar member-avatar-small" src="{{ asset($u->avatar) }}" alt="{{ $u->name }}">
                            @else
                                <span class="member-avatar member-avatar-fallback member-avatar-small">{{ $u->initials }}</span>
                            @endif
                            <strong>{{ $u->name }}</strong>
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
                        <td>
                            <a href="{{ route('reports.index', ['type' => 'Ledger', 'member' => $u->name]) }}" class="btn small primary">
                                View Ledger
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

