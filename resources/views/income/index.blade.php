@extends('layouts.app')

@section('title', 'Income - Marine Harmony')

@section('content')
<h1>Income Entry</h1>

<div class="card">
    <form action="{{ route('income.store') }}" method="POST">
        @csrf
        <div>
            <label for="lDate">Date*</label>
            <input id="lDate" name="date" type="date" required value="{{ date('Y-m-d') }}">
        </div>

        <div>
            <label for="lCategory">Source*</label>
            <select id="lCategory" name="source" required>
                <option value="">Select Source</option>
                @foreach($sources as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lPurpose">Purpose / Title*</label>
            <input id="lPurpose" name="purpose" placeholder="e.g. Bank Profit MTDR 2024" required>
        </div>

        <div>
            <label for="lAmount">Amount (BDT)*</label>
            <input id="lAmount" name="amount" type="number" min="0.01" step="0.01" placeholder="0.00" required>
        </div>

        <div class="full mh-ledger-details">
            <label for="lDetails">Details*</label>
            <textarea id="lDetails" name="details" rows="3" required placeholder="Supporting details and breakdown for this income."></textarea>
        </div>

        <div class="full">
            <label for="lRef">Reference / Remarks</label>
            <input id="lRef" name="ref" placeholder="Optional voucher or document reference">
        </div>

        <div class="full">
            <button type="submit" class="btn primary">
                {{ $isAdmin ? 'Record & Approve Income' : 'Submit for Admin Approval' }}
            </button>
        </div>
    </form>
</div>

<div class="panel card">
    <div class="notice">
        {{ $isAdmin ? 'Admin view: all records are shown below. You can delete or manage records.' : 'View-only: approved income records are shown below.' }}
    </div>

    <div class="tablewrap mt-12">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Submitted By</th>
                    <th>Source</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Reference</th>
                    @if($isAdmin)
                        <th>Admin</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($incomes as $inc)
                    <tr>
                        <td>{{ $inc->date ? $inc->date->format('Y-m-d') : '-' }}</td>
                        <td><strong>{{ $inc->submitted_by ?: '-' }}</strong></td>
                        <td>{{ $inc->source ?: '-' }}</td>
                        <td>{{ $inc->purpose ?: '-' }}</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($inc->amount) }}</strong></td>
                        <td>{{ $inc->details ?: '-' }}</td>
                        <td>
                            <span class="status {{ strtolower($inc->status) }}">{{ $inc->status }}</span>
                        </td>
                        <td>{{ $inc->ref ?: '-' }}</td>
                        @if($isAdmin)
                            <td>
                                <form action="{{ route('income.destroy', $inc->id) }}" method="POST" onsubmit="return confirm('Delete this income record?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger small">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="table-empty-cell">No income records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

