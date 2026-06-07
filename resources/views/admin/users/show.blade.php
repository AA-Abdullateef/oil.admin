@extends('layouts.admin')
@section('title', $user->name)
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" style="color:var(--text-muted);text-decoration:none;">Users</a>
     / <strong>{{ $user->name }}</strong>
@endsection

@section('content')
<div class="grid-2 mb-6" style="align-items:start;">
    <div class="card">
        <div class="card-header"><span class="card-title">Account details</span><span class="badge badge-{{ $user->status }}">{{ $user->status }}</span></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-item-label">Full name</div><div class="detail-item-value">{{ $user->name }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Email</div><div class="detail-item-value">{{ $user->email }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Email status</div><div class="detail-item-value"><span class="badge badge-{{ $user->email_verified_at ? 'active' : 'pending' }}">{{ $user->email_verified_at ? 'verified' : 'unverified' }}</span></div></div>
                <div class="detail-item"><div class="detail-item-label">Phone</div><div class="detail-item-value">{{ $user->phone ?? '-' }}</div></div>
                <div class="detail-item"><div class="detail-item-label">KYC status</div><div class="detail-item-value"><span class="badge badge-{{ $user->profile?->kyc_status ?? 'pending' }}">{{ $user->profile?->kyc_status ?? 'pending' }}</span></div></div>
                <div class="detail-item"><div class="detail-item-label">Country</div><div class="detail-item-value">{{ $user->profile?->country?->name ?? '-' }}</div></div>
                <div class="detail-item"><div class="detail-item-label">State</div><div class="detail-item-value">{{ $user->profile?->state?->name ?? '-' }}</div></div>
            </div>
            <div class="divider"></div>
            @unless($user->email_verified_at)
                <form method="POST" action="{{ route('admin.users.verify-email', $user) }}" style="margin-bottom:12px;" onsubmit="return confirm('Verify this user email?');">
                    @csrf
                    <button class="btn btn-primary btn-sm">Verify email</button>
                </form>
            @endunless
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex gap-2 items-center">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <select name="status" class="form-control" style="width:auto;">
                    @foreach(['active','suspended','banned'] as $status)
                        <option value="{{ $status }}" {{ $user->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-ghost btn-sm">Update status</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Roles</span></div>
        <div class="card-body">
            <div style="margin-bottom:16px;">
                @forelse($user->roles as $role)
                    <div class="flex justify-between items-center" style="padding:8px 0;border-bottom:1px solid var(--border);">
                        <span class="badge" style="background:var(--amber-glow);color:var(--amber);">{{ $role->name }}</span>
                        @if(!\App\Support\Roles\RoleManager::isImmutable($role->slug))
                            <form method="POST" action="{{ route('admin.users.remove-role', $user) }}">
                                @csrf
                                <input type="hidden" name="role_id" value="{{ $role->id }}">
                                <button class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="td-muted">No roles assigned.</div>
                @endforelse
            </div>
            <form method="POST" action="{{ route('admin.users.assign-role', $user) }}" class="flex gap-2">
                @csrf
                <select name="role_id" class="form-control">
                    @foreach(\App\Models\Role::all() as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm">Assign</button>
            </form>
        </div>
    </div>
</div>

<div class="card mb-6" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">Dynamic balances</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Asset</th><th>Quantity</th><th>Value</th></tr></thead>
            <tbody>
            @forelse($balances as $row)
            <tr>
                <td class="td-mono">{{ $loop->iteration }}</td>
                <td><span class="td-mono" style="color:var(--amber);">{{ $row['asset']->symbol }}</span> <span class="td-muted">{{ $row['asset']->name }}</span></td>
                <td class="td-mono">{{ number_format($row['quantity'], 8) }}</td>
                <td class="td-mono">{{ config('app.currency', '$') }}{{ number_format($row['value'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="td-muted" style="text-align:center;padding:24px;">No balances yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Recent transactions</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Reference</th><th>Type</th><th>Direction</th><th>Asset</th><th>Quantity</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($recentTransactions as $tx)
            <tr>
                <td class="td-mono">{{ $loop->iteration }}</td>
                <td class="td-mono">{{ $tx->reference }}</td>
                <td><span class="badge badge-{{ $tx->type }}">{{ $tx->type }}</span></td>
                <td><span class="badge badge-{{ $tx->direction }}">{{ $tx->direction }}</span></td>
                <td class="td-mono">{{ $tx->asset->symbol }}</td>
                <td class="td-mono">{{ number_format($tx->quantity, 8) }}</td>
                <td><span class="badge badge-{{ $tx->status }}">{{ $tx->status }}</span></td>
                <td class="td-muted">{{ $tx->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="td-muted" style="text-align:center;padding:24px;">No transactions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
