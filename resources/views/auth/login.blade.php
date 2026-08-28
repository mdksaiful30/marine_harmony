@extends('layouts.app')

@section('title', 'Login - Marine Harmony')

@section('content')
<div class="login">
    <div class="loginbox">
        <img class="login-logo" src="{{ asset('images/logo.jpg') }}" alt="Marine Harmony Logo">
        <h1>Marine Harmony</h1>
        <p class="login-subtitle">Financial Records Management</p>

        @if($errors->any())
            <div class="notice notice-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="login-form-grid">
            @csrf
            <div>
                <label for="loginUser">Select Member Account</label>
                <select id="loginUser" name="name" required onchange="updateRoleHint(this)">
                    @foreach($members as $m)
                        <option value="{{ $m->name }}" data-role="{{ $m->role }}" {{ old('name') === $m->name || $m->isAdmin() ? 'selected' : '' }}>
                            {{ $m->name }} ({{ $m->isAdmin() ? 'Admin' : 'Member' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="loginPin">Member PIN / Password</label>
                <input id="loginPin" name="pin" type="password" placeholder="Enter your member PIN" required autofocus>
                <small id="roleHint" class="hint hint-block">
                    Admin accounts manage approvals, balance reconciliations, and member records.
                </small>
            </div>

            <button type="submit" class="btn primary login-submit-btn">
                Login to Marine Harmony
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateRoleHint(select) {
    const selectedOption = select.options[select.selectedIndex];
    const role = selectedOption.getAttribute('data-role');
    const hint = document.getElementById('roleHint');
    if (role === 'admin') {
        hint.textContent = 'Admin account: full access to Approval Queue, Member Ledgers, and System Balance.';
    } else {
        hint.textContent = 'Member account: view-only approved ledger, submit deposit slips and expense receipts.';
    }
}
</script>
@endsection

