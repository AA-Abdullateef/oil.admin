<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — {{ config('app.name') }} Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;1,9..144,300&family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --bg-base:      #f6f7fb;
    --bg-surface:   #ffffff;
    --bg-raised:    #f9fafb;
    --bg-overlay:   #eef2f7;
    --border:       #e5e7eb;
    --border-hi:    #d1d5db;
    --text-primary: #111827;
    --text-muted:   #64748b;
    --text-faint:   #94a3b8;
    --amber:        #c47a12;
    --amber-dim:    #e7b565;
    --amber-glow:   #fff4df;
    --green:        #15803d;
    --green-dim:    #e8f7ee;
    --red:          #dc2626;
    --red-dim:      #feecec;
    --blue:         #2563eb;
    --blue-dim:     #eaf1ff;
    --radius:       8px;
    --radius-lg:    12px;
    --sidebar-w:    240px;
    --font-display: 'Fraunces', Georgia, serif;
    --font-body:    'DM Sans', sans-serif;
    --font-mono:    'DM Mono', monospace;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; }
body {
    font-family: var(--font-body);
    background:
        radial-gradient(circle at top left, rgba(196, 122, 18, 0.08), transparent 32rem),
        linear-gradient(180deg, #ffffff 0%, var(--bg-base) 18rem);
    color: var(--text-primary);
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
}

/* ── Sidebar ── */
.sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: rgba(255, 255, 255, 0.96);
    border-right: 1px solid var(--border);
    box-shadow: 12px 0 30px rgba(15, 23, 42, 0.05);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
}
.sidebar-brand {
    padding: 20px 20px 16px;
    border-bottom: 1px solid var(--border);
}
.sidebar-brand .app-name {
    font-family: var(--font-display);
    font-size: 17px;
    font-weight: 500;
    color: var(--text-primary);
    letter-spacing: 0;
    line-height: 1.2;
}
.sidebar-brand .app-label {
    font-size: 10px;
    font-family: var(--font-mono);
    color: var(--amber);
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-top: 2px;
}
.sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
.nav-section-label {
    font-size: 10px;
    font-family: var(--font-mono);
    color: var(--text-faint);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 8px 20px 4px;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 20px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.15s, background 0.15s;
    border-left: 2px solid transparent;
    position: relative;
    min-height: 38px;
}
.nav-item:hover { color: var(--text-primary); background: #f3f4f6; }
.nav-item.active {
    color: var(--amber);
    background: var(--amber-glow);
    border-left-color: var(--amber);
    font-weight: 600;
}
.nav-item svg { width: 15px; height: 15px; flex-shrink: 0; }
.nav-badge {
    margin-left: auto;
    background: var(--red);
    color: #fff;
    font-size: 10px;
    font-family: var(--font-mono);
    padding: 1px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}
.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    font-size: 12px;
    color: var(--text-muted);
}
.sidebar-user { font-weight: 500; color: var(--text-primary); margin-bottom: 4px; }

/* ── Main layout ── */
.main-wrap {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    min-width: 0;
    width: calc(100% - var(--sidebar-w));
}
.topbar {
    min-height: 62px;
    background: rgba(255, 255, 255, 0.9);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: blur(14px);
    gap: 16px;
}
.topbar-title {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 300;
    font-style: italic;
    color: var(--text-muted);
}
.topbar-title strong { font-style: normal; font-weight: 500; color: var(--text-primary); }
.topbar-actions { display: flex; align-items: center; gap: 12px; min-width: 0; flex-wrap: wrap; justify-content: flex-end; }
.page-content { padding: 28px; flex: 1; min-width: 0; width: 100%; max-width: 1560px; }

/* ── Components ── */
.stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
.stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}
.stat-label {
    font-size: 11px;
    font-family: var(--font-mono);
    color: var(--text-muted);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.stat-value {
    font-family: var(--font-mono);
    font-size: 26px;
    font-weight: 500;
    color: var(--text-primary);
    line-height: 1;
}
.stat-value.amber { color: var(--amber); }
.stat-value.green { color: var(--green); }
.stat-value.red   { color: var(--red); }
.stat-sub { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

.card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    min-width: 0;
}
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.card-title { font-size: 13px; font-weight: 500; color: var(--text-primary); }
.card-body { padding: 20px; }

/* ── Table ── */
.table-wrap { overflow-x: auto; width: 100%; }
table { width: 100%; border-collapse: collapse; }
thead th {
    font-size: 10px;
    font-family: var(--font-mono);
    color: var(--text-faint);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 10px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f8fafc; }
td { padding: 12px 16px; font-size: 13px; color: var(--text-primary); vertical-align: middle; }
.td-mono { font-family: var(--font-mono); font-size: 12px; }
.td-muted { color: var(--text-muted); font-size: 12px; }

/* ── Badges ── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-family: var(--font-mono);
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 4px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.badge-pending  { background: var(--amber-glow);  color: var(--amber); }
.badge-active   { background: var(--green-dim);        color: var(--green); }
.badge-confirmed, .badge-approved, .badge-completed { background: var(--green-dim); color: var(--green); }
.badge-processing                                  { background: rgba(59,130,246,.14); color: #60a5fa; }
.badge-rejected, .badge-failed, .badge-cancelled, .badge-banned { background: var(--red-dim);   color: var(--red); }
.badge-suspended, .badge-frozen                       { background: var(--blue-dim);  color: var(--blue); }
.badge-credit { background: var(--green-dim); color: var(--green); }
.badge-debit  { background: var(--red-dim);   color: var(--red); }

/* ── Buttons ── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-size: 12px;
    font-family: var(--font-body);
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: all 0.15s;
    white-space: nowrap;
}
.btn-primary   { background: var(--amber);     color: #ffffff; border-color: var(--amber); box-shadow: 0 6px 14px rgba(196, 122, 18, 0.18); }
.btn-primary:hover { background: #a9630b; }
.btn-ghost     { background: transparent; color: var(--text-muted); border-color: var(--border-hi); }
.btn-ghost:hover { color: var(--text-primary); border-color: var(--border-hi); background: var(--bg-raised); }
.btn-danger    { background: var(--red-dim); color: var(--red); border-color: rgba(242,92,92,0.3); }
.btn-danger:hover { background: rgba(242,92,92,0.2); }
.btn-success   { background: var(--green-dim); color: var(--green); border-color: rgba(61,214,140,0.3); }
.btn-success:hover { background: rgba(61,214,140,0.2); }
.btn-sm { padding: 5px 10px; font-size: 11px; }

/* ── Forms ── */
.form-group { margin-bottom: 18px; }
.form-label {
    display: block;
    font-size: 11px;
    font-family: var(--font-mono);
    color: var(--text-muted);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.form-control {
    width: 100%;
    background: var(--bg-raised);
    border: 1px solid var(--border-hi);
    border-radius: var(--radius);
    color: var(--text-primary);
    font-family: var(--font-body);
    font-size: 13px;
    padding: 9px 12px;
    transition: border-color 0.15s;
    outline: none;
}
.form-control:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(196, 122, 18, 0.12); }
.form-control::placeholder { color: var(--text-faint); }
select.form-control { cursor: pointer; }

/* ── Alerts ── */
.alert {
    padding: 12px 16px;
    border-radius: var(--radius);
    font-size: 13px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.alert-success { background: var(--green-dim); color: var(--green); border: 1px solid rgba(61,214,140,0.2); }
.alert-error   { background: var(--red-dim);   color: var(--red);   border: 1px solid rgba(242,92,92,0.2); }

/* ── Pagination ── */
.pagination { display: flex; align-items: center; gap: 4px; padding: 16px 0 4px; }
.pagination a, .pagination span {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 10px;
    border-radius: var(--radius);
    font-size: 12px; font-family: var(--font-mono);
    background: var(--bg-raised); color: var(--text-muted);
    text-decoration: none; border: 1px solid var(--border);
    transition: all 0.15s;
}
.pagination a:hover { color: var(--text-primary); border-color: var(--border-hi); }
.pagination span.active-page { background: var(--amber-glow); color: var(--amber); border-color: var(--amber-dim); }

/* ── Detail grid ── */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.detail-item {}
.detail-item-label { font-size: 10px; font-family: var(--font-mono); color: var(--text-faint); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 4px; }
.detail-item-value { font-size: 13px; color: var(--text-primary); }
.detail-item-value.mono { font-family: var(--font-mono); }
.detail-item-value.lg { font-size: 22px; font-family: var(--font-mono); font-weight: 500; color: var(--amber); }

.divider { height: 1px; background: var(--border); margin: 20px 0; }
.text-muted { color: var(--text-muted); }
.text-mono  { font-family: var(--font-mono); font-size: 12px; }
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.mb-4 { margin-bottom: 16px; }
.mb-6 { margin-bottom: 24px; }
.grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }

/* ── Search bar ── */
.search-bar {
    display: flex; gap: 8px; align-items: center;
    background: var(--bg-raised);
    border: 1px solid var(--border-hi);
    border-radius: var(--radius);
    padding: 0 12px;
    flex: 1; max-width: 320px;
}
.search-bar input {
    background: none; border: none; outline: none;
    color: var(--text-primary); font-size: 13px;
    padding: 8px 0; width: 100%;
}
.search-bar svg { color: var(--text-faint); flex-shrink: 0; }

@media (max-width: 1180px) {
    .grid-3 { grid-template-columns: 1fr; }
    .grid-2 { grid-template-columns: 1fr; }
}

@media (max-width: 860px) {
    body { display: block; overflow-x: hidden; }
    .sidebar {
        position: relative;
        width: 100%;
        min-height: auto;
        border-right: none;
        border-bottom: 1px solid var(--border);
    }
    .sidebar-nav {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 4px 8px;
        padding: 12px;
        overflow: visible;
    }
    .nav-section-label { grid-column: 1 / -1; padding: 10px 8px 2px; }
    .nav-item { padding: 9px 10px; border-left: 0; border-radius: var(--radius); }
    .sidebar-footer { padding: 14px 20px; }
    .main-wrap { margin-left: 0; width: 100%; }
    .topbar { position: relative; padding: 16px 18px; align-items: flex-start; flex-direction: column; }
    .topbar-actions { width: 100%; justify-content: flex-start; }
    .page-content { padding: 18px; }
    .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .detail-grid { grid-template-columns: 1fr; }
}

@media (max-width: 560px) {
    .sidebar-nav, .stat-grid { grid-template-columns: 1fr; }
    .card-header { align-items: flex-start; flex-direction: column; gap: 10px; }
    .btn { width: 100%; justify-content: center; }
    td, thead th { padding: 10px 12px; }
}
</style>
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="app-name">{{ config('app.name') }}</div>
        <div class="app-label">Admin Console</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            Dashboard
        </a>

        <div class="nav-section-label">Users</div>
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            Users
        </a>
        <a href="{{ route('admin.kyc.index') }}" class="nav-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            KYC Reviews
            @php $pendingKyc = \App\Models\Profile::whereIn('kyc_status',['submitted','under_review'])->count() @endphp
            @if($pendingKyc > 0) <span class="nav-badge">{{ $pendingKyc }}</span> @endif
        </a>

        <div class="nav-section-label">Finance</div>
        <a href="{{ route('admin.deposits.index') }}" class="nav-item {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25"/></svg>
            Deposits
            @php $pendingDep = \App\Models\Deposit::where('status','pending')->count() @endphp
            @if($pendingDep > 0) <span class="nav-badge">{{ $pendingDep }}</span> @endif
        </a>
        <a href="{{ route('admin.withdrawals.index') }}" class="nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0L9 5.25M12 2.25v13.5"/></svg>
            Withdrawals
            @php $pendingWith = \App\Models\Withdrawal::where('status','pending')->count() @endphp
            @if($pendingWith > 0) <span class="nav-badge">{{ $pendingWith }}</span> @endif
        </a>
        <a href="{{ route('admin.balances.index') }}" class="nav-item {{ request()->routeIs('admin.balances.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18-3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3m18-3V6"/></svg>
            Balances
        </a>
        <a href="{{ route('admin.transactions.index') }}" class="nav-item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
            Transactions
        </a>
        <a href="{{ route('admin.sub-methods.index') }}" class="nav-item {{ request()->routeIs('admin.sub-methods.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
            Payment sub-methods
        </a>

        <div class="nav-section-label">Market</div>
        <a href="{{ route('admin.assets.index') }}" class="nav-item {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
            Assets
        </a>
        <a href="{{ route('admin.holdings.index') }}" class="nav-item {{ request()->routeIs('admin.holdings.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5"/></svg>
            Holdings
        </a>
        <a href="{{ route('admin.earning-schedules.index') }}" class="nav-item {{ request()->routeIs('admin.earning-schedules.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Earning schedules
        </a>

        <div class="nav-section-label">Access</div>
        <a href="{{ route('admin.roles.index') }}" class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
            Roles
        </a>
        <a href="{{ route('admin.permissions.index') }}" class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.633c-2.425 0-4.843.816-6.75 2.724A9.373 9.373 0 003.373 17.25M12 6.633v10.683m0-10.683c1.152-.26 2.306-.26 3.457 0a9.373 9.373 0 016.75 6.75c.26 1.152.26 2.306 0 3.457a9.373 9.373 0 01-6.75 6.750c-1.152.26-2.306.26-3.457 0a9.373 9.373 0 01-6.750-6.750c-.26-1.152-.26-2.306 0-3.457a9.373 9.373 0 016.750-6.750z"/></svg>
            Permissions
        </a>

        <div class="nav-section-label">System</div>
        <a href="{{ route('admin.audit-logs.index') }}" class="nav-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5A3.375 3.375 0 0010.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zM9 13.5h6m-6 3h6"/></svg>
            Audit Logs
        </a>
        <a href="{{ route('admin.contact-messages.index') }}" class="nav-item {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 01-2.555-.337L3 21l1.687-4.217A7.74 7.74 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
            Contact Messages
            @php $contactMessages = \App\Models\ContactMessage::count() @endphp
            @if($contactMessages > 0) <span class="nav-badge">{{ $contactMessages }}</span> @endif
        </a>
        <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>
    </nav>

    <div class="sidebar-footer">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <div class="sidebar-user">{{ auth()->user()->name }}</div>
            @if(auth()->user()->isSuperAdmin())
                <span class="badge" style="background:var(--amber-glow);color:var(--amber);
                                            font-size:9px;padding:2px 6px;">
                    super admin
                </span>
            @elseif(auth()->user()->isAdmin())
                <span class="badge" style="background:var(--blue-dim);color:var(--blue);
                                            font-size:9px;padding:2px 6px;">
                    admin
                </span>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" style="background:none;border:none;color:var(--text-muted);
                                        cursor:pointer;font-size:12px;padding:0;">
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- Main --}}
<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-title">
            {!! $__env->yieldContent('breadcrumb', '<strong>Dashboard</strong>') !!}
        </div>

        <div class="topbar-actions">
            @yield('topbar-actions')
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
