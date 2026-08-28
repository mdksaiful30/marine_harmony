<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Marine Harmony')</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/marine-harmony.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inline.css') }}">

    <!-- External Libraries for Exports -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        window.MONTHLY_INSTALLMENT = {{ \App\Services\FinanceService::MONTHLY_INSTALLMENT }};
    </script>
</head>
<body>
    @auth
    <header>
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/logo.jpg') }}" alt="Marine Harmony">
            <span>Marine Harmony</span>
        </div>
        <div class="userbar">
            <a href="{{ route('members.show', Auth::id()) }}" class="user-profile" title="View Your Profile" style="text-decoration: none; color: inherit;">
                @if(Auth::user()->avatar)
                    <img class="member-avatar" src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}">
                @else
                    <span class="member-avatar member-avatar-fallback">{{ Auth::user()->initials }}</span>
                @endif
                <div>
                    <div class="user-name">{{ Auth::user()->name }}</div>
                </div>
            </a>
            <span class="role">{{ Auth::user()->isAdmin() ? 'Admin' : 'Member' }}</span>
            <select onchange="if(this.value) window.location.href=this.value;" style="background: rgba(255,255,255,0.18); color: #fff; border: 1px solid rgba(255,255,255,0.35); border-radius: 8px; padding: 4px 8px; font-size: 12px; font-weight: 600; cursor: pointer; outline: none;" title="Switch Member Account">
                <option value="" disabled style="color: #000;">Switch Member ▾</option>
                @foreach(\App\Models\User::orderBy('id')->get() as $u)
                    <option value="{{ route('auth.switch-member', $u->id) }}" style="color: #000;" {{ Auth::id() === $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->isAdmin() ? 'Admin' : 'Member' }})
                    </option>
                @endforeach
            </select>
            <form action="{{ route('logout') }}" method="POST" class="form-inline">
                @csrf
                <button type="submit" class="btn small danger ml-6">Logout</button>
            </form>
        </div>
    </header>

    <div class="wrap">
        <nav id="nav">
            <a href="{{ route('dashboard') }}" class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('deposits.index') }}" class="nav-btn {{ request()->routeIs('deposits.*') ? 'active' : '' }}">Deposits</a>
            <a href="{{ route('income.index') }}" class="nav-btn {{ request()->routeIs('income.*') ? 'active' : '' }}">Income</a>
            <a href="{{ route('expenses.index') }}" class="nav-btn {{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenditure</a>
            <a href="{{ route('investments.index') }}" class="nav-btn {{ request()->routeIs('investments.*') ? 'active' : '' }}">Investment</a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('approval.index') }}" class="nav-btn {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                    Approval
                    @php $pendingCount = \App\Services\FinanceService::pendingApprovalsCount(); @endphp
                    @if($pendingCount > 0)
                        <span class="pill pill-pending-badge">{{ $pendingCount }}</span>
                    @endif
                </a>
            @endif
            <a href="{{ route('members.index') }}" class="nav-btn {{ request()->routeIs('members.*') ? 'active' : '' }}">Members</a>
            <a href="{{ route('reports.index') }}" class="nav-btn {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>
            @if(Auth::user()->isAdmin())
                <a href="{{ url('/admin') }}" class="nav-btn {{ request()->is('admin*') ? 'active' : '' }}">⚙ Admin Panel</a>
            @endif
        </nav>

        @if(session('success'))
            <div class="notice notice-success">
                <strong>✓ Success:</strong> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="notice notice-error">
                <strong>⚠ Alert:</strong> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="notice notice-error">
                <strong>⚠ Please fix the following errors:</strong>
                <ul class="notice-error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main id="content">
            @yield('content')
        </main>
    </div>
    @else
        @yield('content')
    @endauth

    <!-- Custom JS -->
    <script src="{{ asset('js/marine-harmony.js') }}"></script>
    @yield('scripts')
</body>
</html>

