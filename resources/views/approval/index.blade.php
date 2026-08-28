@extends('layouts.app')

@section('title', 'Approval Queue - Marine Harmony')

@section('content')
<h1>Approval Queue</h1>

<div class="card">
    <div class="notice">
        <strong>{{ $pending->count() }}</strong> record(s) awaiting Admin approval. The "Submitted By" name is the member account that entered the record.
    </div>

    <div class="tablewrap mt-14">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Submitted By</th>
                    <th>Member / Beneficiary</th>
                    <th>Amount</th>
                    <th>Details</th>
                    <th>Receipt</th>
                    <th class="th-admin-action">Admin Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pending as $item)
                    <tr>
                        <td><strong>{{ $item['type'] }}</strong></td>
                        <td>{{ $item['date'] }}</td>
                        <td><strong>{{ $item['submitted_by'] }}</strong></td>
                        <td>{{ $item['label'] }}</td>
                        <td><strong>{{ \App\Services\FinanceService::formatMoney($item['amount']) }}</strong></td>
                        <td>{{ $item['details'] }}</td>
                        <td>
                            @if($item['attachment_path'])
                                <a href="{{ asset($item['attachment_path']) }}" target="_blank" class="btn small btn-doc-link">View Doc</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <form action="{{ route('approval.decide') }}" method="POST" class="form-inline">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $item['type'] }}">
                                    <input type="hidden" name="id" value="{{ $item['id'] }}">
                                    <input type="hidden" name="decision" value="Approved">
                                    <button type="submit" class="btn success small">✓ Approve</button>
                                </form>

                                <button type="button" class="btn danger small" onclick="openRejectModal('{{ $item['type'] }}', '{{ $item['id'] }}')">✗ Reject</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="table-empty-cell-large">
                            <div class="all-clear-icon">✓</div>
                            <strong>All caught up!</strong> No records currently pending approval.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="hidden modal-overlay" onclick="if(event.target === this) closeRejectModal();">
    <div class="card modal-card" onclick="event.stopPropagation();">
        <h2 class="modal-title-danger">Reject Transaction</h2>
        <form action="{{ route('approval.decide') }}" method="POST" id="rejectForm">
            @csrf
            <input type="hidden" name="type" id="rejectType">
            <input type="hidden" name="id" id="rejectId">
            <input type="hidden" name="decision" value="Rejected">

            <div>
                <label for="rejectionReason">Reason for Rejection (Optional):</label>
                <textarea id="rejectionReason" name="rejection_reason" rows="3" placeholder="e.g. Duplicate installment month, incorrect slip image, etc."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn danger">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openRejectModal(type, id) {
    document.getElementById('rejectType').value = type;
    document.getElementById('rejectId').value = id;
    document.getElementById('rejectionReason').value = '';
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});
</script>
@endsection

