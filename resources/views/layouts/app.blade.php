<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Monitoring Kapasitas Mesin Roll Forming 41x41">
    <title>@yield('title', 'RF Monitor') | Monitoring Kapasitas Mesin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://unpkg.com/lucide@latest" defer></script>
    @stack('styles')
</head>
<body>
    <aside class="sidebar" id="sidebar" aria-label="Main Navigation">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <svg viewBox="0 0 32 32" fill="none"><rect x="2" y="8" width="28" height="4" rx="1" fill="var(--accent)"/><rect x="6" y="14" width="20" height="3" rx="1" fill="var(--accent)" opacity="0.6"/><rect x="4" y="19" width="24" height="3" rx="1" fill="var(--accent)" opacity="0.4"/><circle cx="16" cy="27" r="3" fill="var(--accent)"/></svg>
            </div>
            <div class="brand-text">
                <span class="brand-name">RF Monitor</span>
                <span class="brand-sub">Cell 3 / Roll Forming</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('sales-orders.index') }}" class="nav-item {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}" id="nav-sales-orders">
                <i data-lucide="file-text"></i>
                <span>Sales Order</span>
            </a>
            <a href="{{ route('daily-targets.index') }}" class="nav-item {{ request()->routeIs('daily-targets.*') ? 'active' : '' }}" id="nav-daily-targets">
                <i data-lucide="target"></i>
                <span>Target Harian</span>
            </a>
            <a href="{{ route('actual-production.index') }}" class="nav-item {{ request()->routeIs('actual-production.*') ? 'active' : '' }}" id="nav-actual">
                <i data-lucide="activity"></i>
                <span>Aktual Produksi</span>
            </a>
            <a href="{{ route('capacity.index') }}" class="nav-item {{ request()->routeIs('capacity.index') ? 'active' : '' }}" id="nav-capacity">
                <i data-lucide="gauge"></i>
                <span>Kapasitas Mesin</span>
            </a>
            <a href="{{ route('monitoring.index') }}" class="nav-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}" id="nav-monitoring">
                <i data-lucide="monitor"></i>
                <span>Monitoring</span>
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('capacity.settings') }}" class="nav-item {{ request()->routeIs('capacity.settings') ? 'active' : '' }}" id="nav-settings">
                <i data-lucide="settings"></i>
                <span>Pengaturan Mesin</span>
            </a>
            <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}" id="nav-users">
                <i data-lucide="users"></i>
                <span>Kelola User</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-info">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">{{ strtoupper(auth()->user()->role) }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn" id="btn-logout" aria-label="Logout">
                    <i data-lucide="log-out"></i>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                <i data-lucide="menu"></i>
            </button>
            <div class="topbar-left">
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                <span class="page-breadcrumb">@yield('breadcrumb', '')</span>
            </div>
            <div class="topbar-right">
                <span class="topbar-date" id="topbar-date"></span>
                <div class="topbar-actions">
                    @yield('topbar-actions')
                </div>
            </div>
        </header>

        <div class="content-area" id="content-area">
            @if(session('success'))
            <div class="alert alert-success" role="alert" aria-live="polite" id="alert-success">
                <i data-lucide="check-circle-2"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close" aria-label="Close alert">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error" role="alert" aria-live="polite" id="alert-error">
                <i data-lucide="alert-circle"></i>
                <span>{{ session('error') }}</span>
                <button class="alert-close" aria-label="Close alert">&times;</button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error" role="alert" aria-live="polite" id="alert-validation">
                <i data-lucide="alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                <button class="alert-close" aria-label="Close alert">&times;</button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
