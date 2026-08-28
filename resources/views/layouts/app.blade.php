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
            <div class="user-profile">
                @if(Auth::user()->avatar)
                    <img class="member-avatar" src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}">
                @else
                    <span class="member-avatar member-avatar-fallback">{{ Auth::user()->initials }}</span>
                @endif
                <div>
                    <div class="user-name">{{ Auth::user()->name }}</div>
                </div>
            </div>
            <span class="role">{{ Auth::user()->isAdmin() ? 'Admin' : 'Member' }}</span>
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

