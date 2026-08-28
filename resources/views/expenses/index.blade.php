@extends('layouts.app')

@section('title', 'Expenditure - Marine Harmony')

@section('content')
<h1>Expenditure Entry</h1>

<div class="card">
    <form action="{{ route('expenses.store') }}" method="POST">
        @csrf
        <div>
            <label for="lDate">Date*</label>
            <input id="lDate" name="date" type="date" required value="{{ date('Y-m-d') }}">
        </div>

        <div>
            <label for="lCategory">Category*</label>
            <select id="lCategory" name="category" required>
                <option value="">Select Category</option>
                @foreach($categories as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lPurpose">Description / Item*</label>
            <input id="lPurpose" name="description" placeholder="e.g. Bank Service Charge Q1" required>
        </div>

        <div>
            <label for="lAmount">Amount (BDT)*</label>
            <input id="lAmount" name="amount" type="number" min="0.01" step="0.01" placeholder="0.00" required>
        </div>

        <div class="full mh-ledger-details">
            <label for="lDetails">Details*</label>
            <textarea id="lDetails" name="details" rows="3" required placeholder="Supporting details and breakdown for this expenditure."></textarea>
        </div>

        <div class="full">
            <label for="lRef">Reference / Remarks</label>
            <input id="lRef" name="ref" placeholder="Optional voucher or receipt number">
        </div>

        <div class="full">
            <button type="submit" class="btn primary">
                {{ $isAdmin ? 'Record & Approve Expenditure' : 'Submit for Admin Approval' }}
            </button>
        </div>
    </form>
</div>

<div class="panel card">
    <div class="notice">
        {{ $isAdmin ? 'Admin view: all records are shown below. You can delete or manage records.' : 'View-only: approved expenditure records are shown below.' }}
    </div>

    <div class="tablewrap mt-12">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Submitted By</th>
                    <th>Category</th>
                    <th>Description</th>
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
                @forelse($expenses as $exp)
                    <tr>
                        <td>{{ $exp->date ? $exp->date->format('Y-m-d') : '-' }}</td>
                        <td><strong>{{ $exp->submitted_by ?: '-' }}</strong></td>
                        <td>{{ $exp->category ?: '-' }}</td>
                        <td>{{ $exp->description ?: '-' }}</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($exp->amount) }}</strong></td>
                        <td>{{ $exp->details ?: '-' }}</td>
                        <td>
                            <span class="status {{ strtolower($exp->status) }}">{{ $exp->status }}</span>
                        </td>
                        <td>{{ $exp->ref ?: '-' }}</td>
                        @if($isAdmin)
                            <td>
                                <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Delete this expenditure record?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger small">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="table-empty-cell">No expenditure records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

