<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>{{ config('app.name', 'Casa') }}@hasSection('title') – @yield('title')@endif</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Dark mode ── */
        html[data-theme="dark"] {
            --bg:            #1c1a18;
            --surface:       #252220;
            --surface-2:     #2f2c29;
            --surface-3:     #3a3632;
            --text:          #f0ece6;
            --text-muted:    #9e9389;
            --text-light:    #5e574f;
            --border:        #3a3632;
            --border-strong: #4a4540;

            --plant-soft:        rgba(22,163,74,.18);
            --chore-soft:        rgba(59,130,246,.18);
            --maintenance-soft:  rgba(245,158,11,.18);
            --pet-soft:          rgba(139,92,246,.18);
            --other-soft:        rgba(100,116,139,.18);

            --ok-soft:       rgba(22,163,74,.18);
            --warning-soft:  rgba(217,119,6,.18);
            --critical-soft: rgba(220,38,38,.18);

            --shadow:    0 1px 3px rgba(0,0,0,.4), 0 4px 12px rgba(0,0,0,.3);
            --shadow-md: 0 4px 16px rgba(0,0,0,.45), 0 1px 4px rgba(0,0,0,.25);
        }

        :root {
            --bg:           #faf8f5;
            --surface:      #ffffff;
            --surface-2:    #f4f1eb;
            --surface-3:    #ece8df;
            --text:         #2c2825;
            --text-muted:   #7d7268;
            --text-light:   #b5ada4;
            --border:       #e5dfd5;
            --border-strong:#cec6ba;

            --plant:        #16a34a; --plant-soft:  #dcfce7;
            --chore:        #3b82f6; --chore-soft:  #dbeafe;
            --maintenance:  #f59e0b; --maintenance-soft: #fef3c7;
            --pet:          #8b5cf6; --pet-soft:    #ede9fe;
            --other:        #64748b; --other-soft:  #f1f5f9;

            --ok:           #16a34a; --ok-soft:     #dcfce7;
            --warning:      #d97706; --warning-soft:#fef3c7;
            --critical:     #dc2626; --critical-soft:#fee2e2;

            --radius:    14px;
            --radius-sm: 8px;
            --radius-lg: 20px;
            --shadow:    0 1px 3px rgba(44,40,37,.05), 0 4px 12px rgba(44,40,37,.05);
            --shadow-md: 0 4px 16px rgba(44,40,37,.09), 0 1px 4px rgba(44,40,37,.05);

            --sidebar-w:     240px;
            --bottom-nav-h:  68px;
            --header-h:      56px;

            --font-display: 'Lora', Georgia, serif;
            --font-body:    'DM Sans', system-ui, sans-serif;
            --ease:         150ms ease;
        }

        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
        }

        /* ── App shell ── */
        .app-shell { display: flex; min-height: 100vh; }

        /* ── Sidebar (desktop) ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: none;
            flex-direction: column;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 200;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand a {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sidebar-nav {
            flex: 1;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background var(--ease), color var(--ease);
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: var(--surface-2);
            color: var(--text);
        }
        .sidebar-nav a.active { font-weight: 600; }
        .sidebar-nav .icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-divider { height: 1px; background: var(--border); margin: 6px 10px; }
        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .sidebar-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: var(--surface-3);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .sidebar-user-name { font-size: 13px; font-weight: 500; }
        .sidebar-logout-btn {
            width: 100%;
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 7px 12px;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            font-family: var(--font-body);
            transition: background var(--ease), color var(--ease);
        }
        .sidebar-logout-btn:hover { background: var(--surface-2); color: var(--text); }
        .sidebar-footer-row { display: flex; gap: 8px; align-items: center; }
        .sidebar-footer-row .sidebar-logout-btn { flex: 1; }
        .theme-toggle {
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 7px 9px;
            cursor: pointer;
            font-size: 15px;
            color: var(--text-muted);
            transition: background var(--ease), color var(--ease);
            line-height: 1;
            min-height: 34px;
            flex-shrink: 0;
        }
        .theme-toggle:hover { background: var(--surface-2); color: var(--text); }

        /* ── Mobile header ── */
        .mobile-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--header-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 200;
        }
        .mobile-header a {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
        }
        .mobile-header-user {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ── Main content ── */
        .main-content {
            flex: 1;
            padding-top: calc(var(--header-h) + 20px);
            padding-bottom: calc(var(--bottom-nav-h) + 16px);
            padding-left: 16px;
            padding-right: 16px;
        }
        .page-container { max-width: 920px; margin: 0 auto; }

        /* ── Bottom nav (mobile) ── */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--bottom-nav-h);
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding-bottom: env(safe-area-inset-bottom, 0);
            z-index: 200;
        }
        .bn-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            flex: 1;
            padding: 8px 4px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 10px;
            font-weight: 500;
            transition: color var(--ease);
            min-height: 48px;
            justify-content: center;
        }
        .bn-item.active, .bn-item:hover { color: var(--text); }
        button.bn-item { background: none; border: none; cursor: pointer; font-family: var(--font-body); }
        .bn-icon { font-size: 21px; line-height: 1; }
        .bn-add {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: var(--text);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            line-height: 1;
            text-decoration: none;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
            transition: transform var(--ease), box-shadow var(--ease);
        }
        .bn-add:active { transform: scale(0.93); }

        /* ── Desktop layout ── */
        @media (min-width: 768px) {
            .sidebar { display: flex; }
            .mobile-header { display: none; }
            .bottom-nav { display: none; }
            .main-content {
                margin-left: var(--sidebar-w);
                padding: 36px 40px;
            }
        }

        /* ── Flash messages ── */
        .flash {
            padding: 11px 15px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .flash-success { background: var(--ok-soft); color: #14532d; border: 1px solid #bbf7d0; }
        .flash-error   { background: var(--critical-soft); color: #7f1d1d; border: 1px solid #fecaca; }
        html[data-theme="dark"] .flash-success { color: #86efac; border-color: rgba(22,163,74,.35); }
        html[data-theme="dark"] .flash-error   { color: #fca5a5; border-color: rgba(220,38,38,.35); }

        /* ── Typography ── */
        .page-title {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -.4px;
            line-height: 1.2;
        }
        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 3px;
        }
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .page-header-actions { display: flex; gap: 8px; align-items: center; }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all var(--ease);
            line-height: 1;
            min-height: 38px;
            white-space: nowrap;
        }
        .btn-primary { background: #2d5a27; color: #e8f5e2; border: 1px solid #3a7232; }
        .btn-primary:hover { background: #366b2e; box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--surface-2); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--surface-3); }
        .btn-danger { background: var(--critical-soft); color: #991b1b; }
        .btn-danger:hover { background: #fecaca; }

        html[data-theme="dark"] .btn-primary { background: #3a7232; color: #e8f5e2; border-color: #4a8f40; }
        html[data-theme="dark"] .btn-primary:hover { background: #4a8f40; }
        html[data-theme="dark"] .btn-secondary { background: var(--surface-3); color: #d6cfc6; border-color: var(--border-strong); }
        html[data-theme="dark"] .btn-secondary:hover { background: #4a4540; color: var(--text); }
        html[data-theme="dark"] .btn-danger { background: rgba(220,38,38,.2); color: #fca5a5; }
        html[data-theme="dark"] .btn-danger:hover { background: rgba(220,38,38,.3); }

        /* ── Dark mode card boost ── */
        html[data-theme="dark"] .card { border-color: var(--border-strong); }
        .btn-lg { padding: 13px 22px; font-size: 15px; min-height: 48px; }
        .btn-full { width: 100%; }

        /* ── Status badges ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .badge-ok  { background: var(--ok-soft); color: #14532d; }
        .badge-ok .status-dot { background: var(--ok); }
        .badge-warning { background: var(--warning-soft); color: #78350f; }
        .badge-warning .status-dot { background: var(--warning); }
        .badge-critical { background: var(--critical-soft); color: #7f1d1d; }
        .badge-critical .status-dot { background: var(--critical); animation: pulse-dot 1.5s ease-in-out infinite; }

        html[data-theme="dark"] .badge-ok       { color: #86efac; }
        html[data-theme="dark"] .badge-warning  { color: #fcd34d; }
        html[data-theme="dark"] .badge-critical { color: #fca5a5; }
        @keyframes pulse-dot {
            0%,100% { opacity:1; transform:scale(1); }
            50% { opacity:.5; transform:scale(1.4); }
        }

        /* ── Forms ── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: .1px;
        }
        .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .form-input {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-family: var(--font-body);
            color: var(--text);
            background: var(--surface);
            transition: border-color var(--ease), box-shadow var(--ease);
            -webkit-appearance: none;
            appearance: none;
            min-height: 44px;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--text);
            box-shadow: 0 0 0 3px rgba(44,40,37,.07);
        }
        .form-input::placeholder { color: var(--text-light); }
        textarea.form-input { resize: vertical; min-height: 88px; line-height: 1.5; }
        .form-error { font-size: 12px; color: var(--critical); margin-top: 4px; font-weight: 500; }
        .errors-block {
            background: var(--critical-soft);
            border: 1px solid #fecaca;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .errors-block strong { font-size: 13px; color: #7f1d1d; display: block; margin-bottom: 5px; }
        .errors-block ul { margin-left: 16px; font-size: 13px; color: #991b1b; }
        html[data-theme="dark"] .errors-block { border-color: rgba(220,38,38,.35); }
        html[data-theme="dark"] .errors-block strong { color: #fca5a5; }
        html[data-theme="dark"] .errors-block ul { color: #fca5a5; }
    </style>
    @yield('styles')
</head>
<body>
<div class="app-shell">

    {{-- Sidebar (desktop) --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}">🏠 Casa</a>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">🏡</span> Home
            </a>
            <a href="{{ route('items.index', ['category' => 'plant']) }}"
               class="{{ request()->is('items*') && request()->get('category') === 'plant' ? 'active' : '' }}">
                <span class="icon">🌿</span> Plants
            </a>
            <a href="{{ route('items.index', ['category' => 'chore']) }}"
               class="{{ request()->is('items*') && request()->get('category') === 'chore' ? 'active' : '' }}">
                <span class="icon">🧹</span> Chores
            </a>
            <a href="{{ route('items.index', ['category' => 'maintenance']) }}"
               class="{{ request()->is('items*') && request()->get('category') === 'maintenance' ? 'active' : '' }}">
                <span class="icon">🔧</span> Maintenance
            </a>
            <a href="{{ route('items.index', ['category' => 'pet']) }}"
               class="{{ request()->is('items*') && request()->get('category') === 'pet' ? 'active' : '' }}">
                <span class="icon">🐾</span> Pets
            </a>
            <a href="{{ route('items.index', ['category' => 'other']) }}"
               class="{{ request()->is('items*') && request()->get('category') === 'other' ? 'active' : '' }}">
                <span class="icon">📌</span> Other
            </a>
            <div class="sidebar-divider"></div>
            <a href="{{ route('items.create') }}" class="{{ request()->routeIs('items.create') ? 'active' : '' }}">
                <span class="icon">＋</span> Add item
            </a>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="icon">⚙️</span> Settings
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
            </div>
            <div class="sidebar-footer-row">
                <form method="POST" action="{{ route('logout') }}" style="flex:1">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn" style="width:100%">Sign out</button>
                </form>
                <button class="theme-toggle" id="theme-toggle-sidebar" onclick="toggleTheme()" title="Toggle dark mode"></button>
            </div>
        </div>
    </aside>

    {{-- Mobile header --}}
    <header class="mobile-header">
        <a href="{{ route('dashboard') }}">🏠 Casa</a>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="mobile-header-user">{{ auth()->user()->name }}</span>
            <button class="theme-toggle" id="theme-toggle-mobile" onclick="toggleTheme()" title="Toggle dark mode"></button>
        </div>
    </header>

    {{-- Main --}}
    <main class="main-content">
        <div class="page-container">

            @if ($errors->any())
                <div class="errors-block">
                    <strong>Please fix the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="flash flash-success">✓ {{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

{{-- Bottom nav (mobile) --}}
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}"
       class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="bn-icon">🏡</span>
        <span>Home</span>
    </a>
    <a href="{{ route('items.index', ['category' => 'plant']) }}"
       class="bn-item {{ request()->get('category') === 'plant' ? 'active' : '' }}">
        <span class="bn-icon">🌿</span>
        <span>Plants</span>
    </a>
    <a href="{{ route('items.create') }}" class="bn-add">＋</a>
    <a href="{{ route('items.index', ['category' => 'chore']) }}"
       class="bn-item {{ request()->get('category') === 'chore' ? 'active' : '' }}">
        <span class="bn-icon">🧹</span>
        <span>Chores</span>
    </a>
    <button class="bn-item" id="more-btn" onclick="toggleMoreDrawer()">
        <span class="bn-icon">⋯</span>
        <span>More</span>
    </button>
</nav>
{{-- More drawer (mobile) --}}
<div id="more-overlay" onclick="toggleMoreDrawer()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:300; backdrop-filter:blur(2px);"></div>

<div id="more-drawer"
     style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:301;
            background:var(--surface); border-radius:20px 20px 0 0;
            border-top:1px solid var(--border); padding:12px 16px 32px;
            transform:translateY(100%); transition:transform 250ms ease;">

    <div style="width:36px; height:4px; background:var(--border); border-radius:2px; margin:0 auto 20px;"></div>

    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:10px; padding:0 4px;">More categories</div>

    <nav style="display:flex; flex-direction:column; gap:2px; margin-bottom:16px;">
        <a href="{{ route('items.index', ['category' => 'maintenance']) }}"
           style="display:flex; align-items:center; gap:12px; padding:13px 12px; border-radius:10px;
                  text-decoration:none; color:var(--text); font-size:15px; font-weight:500;
                  transition:background var(--ease);"
           onmouseenter="this.style.background='var(--surface-2)'"
           onmouseleave="this.style.background=''">
            <span style="font-size:22px; width:28px; text-align:center;">🔧</span> Maintenance
        </a>
        <a href="{{ route('items.index', ['category' => 'pet']) }}"
           style="display:flex; align-items:center; gap:12px; padding:13px 12px; border-radius:10px;
                  text-decoration:none; color:var(--text); font-size:15px; font-weight:500;
                  transition:background var(--ease);"
           onmouseenter="this.style.background='var(--surface-2)'"
           onmouseleave="this.style.background=''">
            <span style="font-size:22px; width:28px; text-align:center;">🐾</span> Pets
        </a>
        <a href="{{ route('items.index', ['category' => 'other']) }}"
           style="display:flex; align-items:center; gap:12px; padding:13px 12px; border-radius:10px;
                  text-decoration:none; color:var(--text); font-size:15px; font-weight:500;
                  transition:background var(--ease);"
           onmouseenter="this.style.background='var(--surface-2)'"
           onmouseleave="this.style.background=''">
            <span style="font-size:22px; width:28px; text-align:center;">📌</span> Other
        </a>
    </nav>

    <div style="height:1px; background:var(--border); margin-bottom:14px;"></div>

    <nav style="display:flex; flex-direction:column; gap:2px;">
        <a href="{{ route('settings.index') }}"
           style="display:flex; align-items:center; gap:12px; padding:13px 12px; border-radius:10px;
                  text-decoration:none; color:var(--text); font-size:15px; font-weight:500;
                  transition:background var(--ease);"
           onmouseenter="this.style.background='var(--surface-2)'"
           onmouseleave="this.style.background=''">
            <span style="font-size:22px; width:28px; text-align:center;">⚙️</span> Settings
        </a>
        <button onclick="toggleTheme()"
           style="display:flex; align-items:center; gap:12px; padding:13px 12px; border-radius:10px;
                  color:var(--text); font-size:15px; font-weight:500; width:100%; text-align:left;
                  background:none; border:none; cursor:pointer; font-family:var(--font-body);
                  transition:background var(--ease);"
           onmouseenter="this.style.background='var(--surface-2)'"
           onmouseleave="this.style.background=''">
            <span id="drawer-theme-icon" style="font-size:22px; width:28px; text-align:center;"></span>
            <span id="drawer-theme-label">Toggle dark mode</span>
        </button>
    </nav>
</div>

<script>
    const ICONS = { light: '☀️', dark: '🌙' };

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('casa-theme', theme);
        const toggleIcon  = theme === 'dark' ? ICONS.light : ICONS.dark;
        const toggleLabel = theme === 'dark' ? 'Light mode' : 'Dark mode';
        document.querySelectorAll('.theme-toggle').forEach(btn => btn.textContent = toggleIcon);
        const di = document.getElementById('drawer-theme-icon');
        const dl = document.getElementById('drawer-theme-label');
        if (di) di.textContent = toggleIcon;
        if (dl) dl.textContent = toggleLabel;
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
    }

    // More drawer
    function toggleMoreDrawer() {
        const overlay = document.getElementById('more-overlay');
        const drawer  = document.getElementById('more-drawer');
        const isOpen  = drawer.style.display === 'block';
        if (isOpen) {
            drawer.style.transform = 'translateY(100%)';
            setTimeout(() => { drawer.style.display = 'none'; overlay.style.display = 'none'; }, 250);
        } else {
            overlay.style.display = 'block';
            drawer.style.display  = 'block';
            requestAnimationFrame(() => drawer.style.transform = 'translateY(0)');
        }
    }

    // On load: use saved preference, then system preference
    (function() {
        const saved  = localStorage.getItem('casa-theme');
        const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        applyTheme(saved || system);
    })();
</script>
@stack('scripts')
</body>
</html>
