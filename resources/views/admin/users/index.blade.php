@extends('layouts.admin')
@section('title', 'Users')
@section('breadcrumb')
    Manage / <strong>Users</strong>
@endsection

@section('topbar-actions')
    <form class="flex gap-2 items-center" method="GET">
        <div class="search-bar">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" placeholder="Search name or email…" value="{{ request('search') }}">
        </div>
        <select name="status" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            <option value="banned"    {{ request('status') === 'banned'    ? 'selected' : '' }}>Banned</option>
        </select>
        <button type="submit" class="btn btn-ghost">Filter</button>
    </form>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>User</th>
                    <th>Phone</th>
                    <th>Roles</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
            <tr>
                <td class="td-mono">{{ $users->firstItem() + $loop->index }}</td>
                <td>
                    <div style="font-weight:500;">{{ $user->name }}</div>
                    <div class="td-muted">{{ $user->email }}</div>
                </td>
                <td class="td-muted">{{ $user->phone ?? '—' }}</td>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge badge-{{ $role->slug === 'admin' ? 'amber' : 'active' }}" style="{{ $role->slug === 'admin' ? 'background:var(--amber-glow);color:var(--amber);' : '' }}">
                            {{ $role->slug }}
                        </span>
                    @endforeach
                </td>
                <td>
                    @if($user->email_verified_at)
                        <span class="badge badge-active">Verified</span>
                    @else
                        <form method="POST" action="{{ route('admin.users.verify-email', $user) }}" onsubmit="return confirm('Verify this user email?');">
                            @csrf
                            <button class="btn btn-ghost btn-sm">Verify email</button>
                        </form>
                    @endif
                </td>
                <td><span class="badge badge-{{ $user->status }}">{{ $user->status }}</span></td>
                <td class="td-muted">{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="td-muted" style="text-align:center;padding:32px;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div style="padding:0 16px 12px;">
        {{ $users->links('admin.partials.pagination') }}
    </div>
    @endif
</div>
@endsection
