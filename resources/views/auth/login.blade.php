@extends('layouts.app')

@section('title', 'Login - Marine Harmony')

@section('content')
<div class="login">
    <div class="loginbox">
        <!-- Logo & Header -->
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <img class="login-logo" src="{{ asset('images/logo.jpg') }}" alt="Marine Harmony Logo">
            <h1>Marine Harmony</h1>
            <p class="login-subtitle">Financial Records & Management Portal</p>
        </div>

        <!-- Flash Messages & Error Notices -->
        @if(session('success'))
            <div class="notice" style="background: #f0fdf4; border-color: #86efac; color: #166534; margin-bottom: 1rem; border-radius: 12px;">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="notice notice-error" style="background: #fef2f2; border-color: #fca5a5; color: #991b1b; margin-bottom: 1rem; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        <!-- Mode Toggle Tabs (Member Select vs Email/Username) -->
        <div style="display: flex; background: #eef5f8; border-radius: 12px; padding: 4px; margin-bottom: 1.25rem; border: 1px solid #d7e4eb;">
            <button type="button" id="tabMember" onclick="switchLoginMode('member')" style="flex: 1; border: 0; padding: 8px 12px; border-radius: 9px; font-weight: 700; font-size: 13px; cursor: pointer; background: #1683c7; color: #fff; transition: all 0.2s;">
                Quick Member Select
            </button>
            <button type="button" id="tabManual" onclick="switchLoginMode('manual')" style="flex: 1; border: 0; padding: 8px 12px; border-radius: 9px; font-weight: 700; font-size: 13px; cursor: pointer; background: transparent; color: var(--navy); transition: all 0.2s;">
                Email / Username
            </button>
        </div>

        <form action="{{ route('login.post') }}" method="POST" id="loginForm" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf

            <!-- 1. Member Quick Select Container -->
            <div id="memberSelectContainer">
                <label for="loginUser" style="display: block; font-size: 13px; font-weight: 700; color: var(--navy); margin-bottom: 6px;">
                    Select Your Member Account
                </label>
                <div style="position: relative;">
                    <select id="loginUser" name="name" onchange="updateRoleHint(this)" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 14px; background: #fff; font-weight: 600;">
                        @foreach($members as $m)
                            <option value="{{ $m->name }}" data-role="{{ $m->role }}" {{ (old('name') === $m->name || (empty(old('name')) && $m->isAdmin())) ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->isAdmin() ? 'Admin' : 'Member' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. Manual Username/Email Container (Hidden by default) -->
            <div id="manualLoginContainer" style="display: none;">
                <label for="loginManualInput" style="display: block; font-size: 13px; font-weight: 700; color: var(--navy); margin-bottom: 6px;">
                    Email or Username
                </label>
                <input id="loginManualInput" name="login" type="text" placeholder="Enter your username or email address" value="{{ old('login') }}" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 14px; background: #fff;">
            </div>

            <!-- Password / PIN with Toggle Visibility -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="loginPin" style="font-size: 13px; font-weight: 700; color: var(--navy);">
                        Member PIN / Password
                    </label>
                    <a href="#forgotModal" onclick="showHelpModal(event)" style="font-size: 12px; color: var(--blue); text-decoration: none; font-weight: 600;">
                        Forgot PIN?
                    </a>
                </div>

                <div style="position: relative; display: flex; align-items: center;">
                    <input id="loginPin" name="pin" type="password" placeholder="Enter your PIN or password" required autofocus style="width: 100%; padding: 11px 45px 11px 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 14px; background: #fff; transition: border-color 0.2s;">

                    <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--muted); padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
                        <svg id="eyeIconOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eyeIconClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>

                <!-- Dynamic Role Hint -->
                <div id="roleHintBox" style="margin-top: 8px; font-size: 12px; color: var(--muted); line-height: 1.4; padding: 6px 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <span id="roleHint">Admin accounts manage approvals, balance reconciliations, and member records.</span>
                </div>
            </div>

            <!-- Remember Me & Security Check -->
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text); user-select: none; font-weight: 500;">
                    <input type="checkbox" name="remember" value="1" checked style="width: 16px; height: 16px; accent-color: var(--blue); cursor: pointer;">
                    <span>Remember me on this device</span>
                </label>
                <span style="font-size: 11px; color: #16a34a; font-weight: 700; display: flex; align-items: center; gap: 3px;">
                    🔒 SSL Secured
                </span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn primary" style="width: 100%; padding: 13px; font-size: 15px; font-weight: 800; border-radius: 12px; margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                <span>Sign In to Marine Harmony</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>
</div>

<!-- Help / Forgot PIN Modal -->
<div id="helpModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 20px; max-width: 440px; width: 100%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                🔑
            </div>
            <div>
                <h3 style="margin: 0; font-size: 16px; color: var(--navy);">PIN / Password Assistance</h3>
                <span style="font-size: 12px; color: var(--muted);">Marine Harmony Member Access</span>
            </div>
        </div>
        <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 16px;">
            For security reasons, member accounts are initialized with their designated PIN. If you have forgotten your credentials or need a PIN reset, please contact the administrator (<strong>Mohammad Nizam Uddin</strong>) directly.
        </p>
        <div style="text-align: right;">
            <button type="button" onclick="closeHelpModal()" class="btn primary" style="padding: 8px 18px; font-size: 13px; border-radius: 8px;">
                Understood
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchLoginMode(mode) {
    const memberBtn = document.getElementById('tabMember');
    const manualBtn = document.getElementById('tabManual');
    const memberContainer = document.getElementById('memberSelectContainer');
    const manualContainer = document.getElementById('manualLoginContainer');
    const userSelect = document.getElementById('loginUser');
    const manualInput = document.getElementById('loginManualInput');

    if (mode === 'member') {
        memberBtn.style.background = '#1683c7';
        memberBtn.style.color = '#fff';
        manualBtn.style.background = 'transparent';
        manualBtn.style.color = 'var(--navy)';

        memberContainer.style.display = 'block';
        manualContainer.style.display = 'none';
        userSelect.removeAttribute('disabled');
        manualInput.setAttribute('disabled', 'disabled');
    } else {
        manualBtn.style.background = '#1683c7';
        manualBtn.style.color = '#fff';
        memberBtn.style.background = 'transparent';
        memberBtn.style.color = 'var(--navy)';

        memberContainer.style.display = 'none';
        manualContainer.style.display = 'block';
        manualInput.removeAttribute('disabled');
        userSelect.setAttribute('disabled', 'disabled');
        manualInput.focus();
    }
}

function togglePasswordVisibility() {
    const pinInput = document.getElementById('loginPin');
    const eyeOpen = document.getElementById('eyeIconOpen');
    const eyeClosed = document.getElementById('eyeIconClosed');

    if (pinInput.type === 'password') {
        pinInput.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        pinInput.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

function updateRoleHint(select) {
    const selectedOption = select.options[select.selectedIndex];
    const role = selectedOption.getAttribute('data-role');
    const hint = document.getElementById('roleHint');
    if (role === 'admin') {
        hint.textContent = 'Admin account: full access to Approval Queue, Dynamic CRUD, and System Balance.';
    } else {
        hint.textContent = 'Member account: view-only approved ledger, submit deposit slips and expense receipts.';
    }
}

function showHelpModal(e) {
    if (e) e.preventDefault();
    document.getElementById('helpModal').style.display = 'flex';
}

function closeHelpModal() {
    document.getElementById('helpModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('loginUser');
    if (select) updateRoleHint(select);
});
</script>
@endsection


