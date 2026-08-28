@extends('layouts.app')

@section('title', $member->name . ' - Member Profile | Marine Harmony')

@section('content')
<div class="member-profile-container">
    <!-- Breadcrumb & Back Navigation -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--muted-text);">
            <a href="{{ route('members.index') }}" style="color: var(--teal); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Members Directory
            </a>
            <span>/</span>
            <span style="color: var(--navy); font-weight: 700;">{{ $member->name }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('reports.index', ['type' => 'Ledger', 'member' => $member->name]) }}" class="btn small" style="background: #e2e8f0; color: #1e293b; font-weight: 600; text-decoration: none; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                Detailed Ledger
            </a>
            <a href="{{ route('deposits.index') }}" class="btn small primary" style="font-weight: 600; text-decoration: none; border-radius: 8px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Submit Deposit
            </a>
        </div>
    </div>

    <!-- Profile Hero Card -->
    <div class="panel card" style="margin-bottom: 1.5rem; padding: 1.75rem; background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(240,249,255,0.95)); border: 1px solid rgba(22,131,199,0.18); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,38,66,0.06);">
        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
            <!-- Avatar -->
            <div style="position: relative;">
                @if($member->avatar)
                    <img src="{{ asset($member->avatar) }}" alt="{{ $member->name }}" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 8px 24px rgba(7,59,102,0.22);">
                @else
                    <div style="width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, #1683c7, #0aa6a6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; border: 4px solid #fff; box-shadow: 0 8px 24px rgba(7,59,102,0.22); letter-spacing: 1px;">
                        {{ $member->initials }}
                    </div>
                @endif
                <span style="position: absolute; bottom: 2px; right: 2px; width: 22px; height: 22px; border-radius: 50%; background: #10b981; border: 3px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.15);" title="Active Member"></span>
            </div>

            <!-- Identity Info -->
            <div style="flex: 1; min-width: 220px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                    <h1 style="margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--navy); letter-spacing: -0.02em;">
                        {{ $member->name }}
                    </h1>
                    <span class="pill pill-role-tag" style="background: {{ $member->isAdmin() ? '#fee2e2' : '#e0f2fe' }}; color: {{ $member->isAdmin() ? '#991b1b' : '#0369a1' }}; font-weight: 700; font-size: 12px; padding: 3px 10px; border-radius: 999px;">
                        {{ $member->isAdmin() ? 'Project Admin' : 'Active Member' }}
                    </span>
                    <span class="mh-ledger-status {{ $summary['status_class'] }}" style="font-size: 12px; padding: 3px 10px; border-radius: 999px; font-weight: 700;">
                        {{ $summary['status_label'] }}
                    </span>
                </div>

                <div style="display: flex; gap: 18px; flex-wrap: wrap; font-size: 13px; color: #64748b;">
                    @if($member->username)
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: #1683c7;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <strong>@ {{ $member->username }}</strong>
                        </span>
                    @endif
                    @if($member->email)
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: #1683c7;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            {{ $member->email }}
                        </span>
                    @endif
                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: #1683c7;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Member ID: <strong>#MEM-{{ sprintf('%03d', $member->id) }}</strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Performance Stat Cards (4 Grid) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Card 1: Total Deposited -->
        <div class="panel card" style="padding: 1.25rem; border-radius: 16px; border-left: 4px solid #1683c7;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                Total Approved Deposits
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #073b66; line-height: 1.2;">
                {{ \App\Services\FinanceService::formatMoney($summary['total_deposited']) }}
            </div>
            <div style="font-size: 12px; color: #10b981; font-weight: 600; margin-top: 6px;">
                ✓ Verified in General Fund
            </div>
        </div>

        <!-- Card 2: Installments Paid -->
        <div class="panel card" style="padding: 1.25rem; border-radius: 16px; border-left: 4px solid #10b981;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                Installments Completed
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #047857; line-height: 1.2;">
                {{ $summary['paid_months'] }} <span style="font-size: 1rem; font-weight: 600; color: #64748b;">Month(s)</span>
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 6px;">
                Rate: BDT 5,000.00 / month
            </div>
        </div>

        <!-- Card 3: Pending & Dues -->
        <div class="panel card" style="padding: 1.25rem; border-radius: 16px; border-left: 4px solid {{ $summary['due_count'] > 0 ? '#ef4444' : '#0aa6a6' }};">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                Installment Dues
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: {{ $summary['due_count'] > 0 ? '#b91c1c' : '#0f766e' }}; line-height: 1.2;">
                @if($summary['due_count'] > 0)
                    {{ $summary['due_count'] }} <span style="font-size: 1rem; font-weight: 600;">Month(s) Due</span>
                @else
                    0 <span style="font-size: 1rem; font-weight: 600;">Due (Up to Date)</span>
                @endif
            </div>
            <div style="font-size: 12px; color: {{ $summary['pending_months'] > 0 ? '#f59e0b' : '#64748b' }}; margin-top: 6px;">
                @if($summary['pending_months'] > 0)
                    ⏳ {{ $summary['pending_months'] }} month(s) pending approval
                @else
                    All target periods cleared
                @endif
            </div>
        </div>

        <!-- Card 4: Total Transactions -->
        <div class="panel card" style="padding: 1.25rem; border-radius: 16px; border-left: 4px solid #8b5cf6;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                Transaction Submissions
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #6d28d9; line-height: 1.2;">
                {{ $deposits->count() }} <span style="font-size: 1rem; font-weight: 600; color: #64748b;">Record(s)</span>
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 6px;">
                {{ $deposits->where('status', 'Approved')->count() }} Approved | {{ $deposits->where('status', 'Pending')->count() }} Pending
            </div>
        </div>
    </div>

    <!-- Month-by-Month Installment Timeline Matrix -->
    <div class="panel card" style="margin-bottom: 1.5rem; padding: 1.5rem; border-radius: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--navy);">
                    Monthly Installment Schedule & Coverage
                </h2>
                <p style="margin: 4px 0 0; font-size: 13px; color: #64748b;">
                    Chronological payment breakdown starting from project inception ({{ \App\Services\FinanceService::START_MONTH }}).
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 14px; font-size: 12px; font-weight: 600;">
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></span> Paid
                </span>
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b;"></span> Pending
                </span>
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></span> Due
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px;">
            @foreach($monthsList as $ym)
                @php
                    $isApproved = !empty($summary['approved_map'][$ym]);
                    $isPending = !empty($summary['pending_map'][$ym]);
                    $isPastOrCurrent = $ym <= \App\Services\FinanceService::getTargetDueMonth();
                    $label = \App\Services\FinanceService::monthLabel($ym);
                @endphp
                <div style="padding: 10px 12px; border-radius: 10px; border: 1px solid {{ $isApproved ? '#bbf7d0' : ($isPending ? '#fde68a' : ($isPastOrCurrent ? '#fecaca' : '#e2e8f0')) }}; background: {{ $isApproved ? '#f0fdf4' : ($isPending ? '#fffbeb' : ($isPastOrCurrent ? '#fef2f2' : '#f8fafc')) }}; text-align: center;">
                    <div style="font-size: 13px; font-weight: 700; color: {{ $isApproved ? '#166534' : ($isPending ? '#92400e' : ($isPastOrCurrent ? '#991b1b' : '#64748b')) }};">
                        {{ $label }}
                    </div>
                    <div style="margin-top: 4px; font-size: 11px; font-weight: 700;">
                        @if($isApproved)
                            <span style="color: #15803d;">✓ PAID</span>
                        @elseif($isPending)
                            <span style="color: #b45309;">⏳ PENDING</span>
                        @elseif($isPastOrCurrent)
                            <span style="color: #b91c1c;">✕ DUE</span>
                        @else
                            <span style="color: #94a3b8;">UPCOMING</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Deposit Transactions Ledger Table -->
    <div class="panel card" style="padding: 1.5rem; border-radius: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--navy);">
                    Deposit Transactions & Payment History
                </h2>
                <p style="margin: 4px 0 0; font-size: 13px; color: #64748b;">
                    Full audit history of all bank, mobile, and cash deposits submitted for {{ $member->name }}.
                </p>
            </div>
        </div>

        @if($deposits->count() > 0)
        <div class="tablewrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Period(s)</th>
                        <th>Method</th>
                        <th>Transaction Details</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Submitted By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deposits as $d)
                    <tr>
                        <td>
                            <strong>{{ $d->date ? $d->date->format('d M Y') : '—' }}</strong>
                        </td>
                        <td>
                            @if($d->period)
                                @foreach($d->periods_list as $p)
                                    <span class="pill" style="font-size: 11px; margin-right: 2px;">{{ \App\Services\FinanceService::monthLabel($p) }}</span>
                                @endforeach
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="pill" style="background: #f1f5f9; color: #334155; font-weight: 600;">
                                {{ $d->method }}
                            </span>
                        </td>
                        <td style="font-size: 13px;">
                            @if($d->method === 'Bank Transfer')
                                <div><strong>{{ $d->bank_name ?? 'Bank' }}</strong> ({{ $d->branch ?? 'Main' }})</div>
                                @if($d->bank_ref)<div style="color: #64748b; font-size: 11px;">Ref: {{ $d->bank_ref }}</div>@endif
                            @elseif($d->method === 'bKash' || $d->method === 'Nagad' || $d->method === 'Rocket' || $d->method === 'Mobile Wallet')
                                <div><strong>{{ $d->mobile_wallet ?? $d->method }}</strong> ({{ $d->mobile_number ?? '—' }})</div>
                                @if($d->mobile_ref)<div style="color: #64748b; font-size: 11px;">TxID: {{ $d->mobile_ref }}</div>@endif
                            @else
                                <div><strong>Cash</strong> ({{ $d->receiver_name ?? 'Treasurer' }})</div>
                            @endif
                            @if($d->remarks)
                                <div style="color: #475569; font-style: italic; font-size: 11px; margin-top: 2px;">"{{ $d->remarks }}"</div>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #073b66; font-size: 14px;">
                                {{ \App\Services\FinanceService::formatMoney($d->amount) }}
                            </strong>
                        </td>
                        <td>
                            @if($d->status === 'Approved')
                                <span class="pill" style="background: #dcfce7; color: #15803d; font-weight: 700;">✓ Approved</span>
                            @elseif($d->status === 'Pending')
                                <span class="pill" style="background: #fef3c7; color: #b45309; font-weight: 700;">⏳ Pending</span>
                            @else
                                <span class="pill" style="background: #fee2e2; color: #b91c1c; font-weight: 700;">✕ Rejected</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: #64748b;">
                            {{ $d->submitted_by ?? 'System' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align: center; padding: 2.5rem; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 44px; height: 44px; color: #94a3b8; margin-bottom: 8px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <h3 style="margin: 0 0 4px; font-size: 16px; color: var(--navy);">No Deposit Records Found</h3>
            <p style="margin: 0; font-size: 13px; color: #64748b;">No transaction deposits have been recorded yet for {{ $member->name }}.</p>
        </div>
        @endif
    </div>
</div>
@endsection
