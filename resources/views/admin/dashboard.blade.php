@extends('layouts.admin')
@section('title', 'Dashboard')
@section('breadcrumb')
    <strong>Dashboard</strong>
@endsection

@section('content')
<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-label">Total users</div>
        <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
        <div class="stat-sub">{{ number_format($stats['active_users']) }} active</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Net ledger value</div>
        <div class="stat-value amber">{{ config('app.currency', '$') }}{{ number_format($stats['total_wallet_balance'], 2) }}</div>
        <div class="stat-sub">Completed credits minus debits</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending deposits</div>
        <div class="stat-value {{ $stats['pending_deposits'] > 0 ? 'amber' : '' }}">{{ $stats['pending_deposits'] }}</div>
        <div class="stat-sub"><a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" style="color:var(--amber);text-decoration:none;">Review →</a></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending withdrawals</div>
        <div class="stat-value {{ $stats['pending_withdrawals'] > 0 ? 'amber' : '' }}">{{ $stats['pending_withdrawals'] }}</div>
        <div class="stat-sub"><a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" style="color:var(--amber);text-decoration:none;">Review →</a></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Monthly credits</div>
        <div class="stat-value green">{{ config('app.currency', '$') }}{{ number_format($stats['monthly_credits'], 2) }}</div>
        <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Monthly debits</div>
        <div class="stat-value red">{{ config('app.currency', '$') }}{{ number_format($stats['monthly_debits'], 2) }}</div>
        <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active assets</div>
        <div class="stat-value">{{ $stats['total_assets'] }}</div>
        <div class="stat-sub">Listed on platform</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total transactions</div>
        <div class="stat-value">{{ number_format($stats['total_transactions']) }}</div>
        <div class="stat-sub">All time</div>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Needs attention</span>
        <span class="td-muted">Items admins should review first</span>
    </div>
    <div class="card-body">
        <div class="stat-grid">
            @foreach($needsAttention as $item)
                <a href="{{ $item['href'] }}" class="stat-card" style="text-decoration:none;box-shadow:none;border-color:var(--border);">
                    <div class="stat-label">{{ $item['label'] }}</div>
                    <div class="stat-value {{ $item['tone'] === 'red' ? 'red' : ($item['tone'] === 'amber' ? 'amber' : '') }}">
                        {{ number_format($item['count']) }}
                    </div>
                    <div class="stat-sub">Open review</div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="grid-2 mb-6" style="align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Asset price charts</span>
            <span class="td-muted">Last 30 days</span>
        </div>
        <div class="card-body">
            @forelse($chartAssets as $asset)
                <div style="padding:14px 0;border-bottom:1px solid var(--border);">
                    <div class="flex justify-between items-center gap-3" style="margin-bottom:10px;">
                        <div style="min-width:0;">
                            <div style="font-weight:600;">{{ $asset['symbol'] }}</div>
                            <div class="td-muted" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $asset['name'] }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div class="td-mono" style="font-weight:600;">{{ $asset['currency'] }} {{ number_format($asset['current_price'], 2) }}</div>
                            <div class="td-mono" style="color:{{ $asset['change'] >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                {{ $asset['change'] >= 0 ? '+' : '' }}{{ number_format($asset['change'], 2) }}%
                            </div>
                        </div>
                    </div>
                    <svg viewBox="0 0 100 40" preserveAspectRatio="none" style="width:100%;height:54px;display:block;">
                        <polyline
                            fill="none"
                            stroke="{{ $asset['change'] >= 0 ? 'var(--green)' : 'var(--red)' }}"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            points="{{ $asset['points'] }}"
                        />
                    </svg>
                </div>
            @empty
                <div class="td-muted" style="text-align:center;padding:24px;">No asset price data yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent audit activity</span>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>S/N</th><th>Action</th><th>Actor</th><th>When</th></tr></thead>
                <tbody>
                @forelse($recentAuditLogs as $log)
                    <tr>
                        <td class="td-mono">{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight:500;">{{ str_replace('_', ' ', $log->event) }}</div>
                            <div class="td-muted">{{ class_basename($log->auditable_type) }} {{ $log->auditable_id ? '#'.substr($log->auditable_id, 0, 8) : '' }}</div>
                        </td>
                        <td class="td-muted">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="td-muted">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="td-muted" style="text-align:center;padding:24px;">No audit activity yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid-3" style="gap:20px;">
    {{-- Recent deposits --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent deposits</span>
            <a href="{{ route('admin.deposits.index') }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>S/N</th><th>User</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentDeposits as $dep)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-size:13px;">{{ $dep->user->name }}</div>
                        <div class="td-muted">{{ $dep->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="td-mono">{{ $dep->asset?->symbol }} {{ number_format($dep->quantity, 4) }}</td>
                    <td><span class="badge badge-{{ $dep->status }}">{{ $dep->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="td-muted" style="text-align:center;padding:20px;">No deposits yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent withdrawals --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent withdrawals</span>
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>S/N</th><th>User</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentWithdrawals as $w)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-size:13px;">{{ $w->user->name }}</div>
                        <div class="td-muted">{{ $w->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="td-mono">{{ $w->asset?->symbol }} {{ number_format($w->quantity, 4) }}</td>
                    <td><span class="badge badge-{{ $w->status }}">{{ $w->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="td-muted" style="text-align:center;padding:20px;">No withdrawals yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent users --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">New users</span>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>S/N</th><th>Name</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentUsers as $u)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-size:13px;">{{ $u->name }}</div>
                        <div class="td-muted">{{ $u->email }}</div>
                    </td>
                    <td><span class="badge badge-{{ $u->status }}">{{ $u->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="3" class="td-muted" style="text-align:center;padding:20px;">No users yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
