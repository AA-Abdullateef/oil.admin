@extends('layouts.admin')
@section('title', 'Roles')
@section('breadcrumb')
    Access / <strong>Roles</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">+ New role</a>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Role</th><th>Slug</th><th>Permissions</th><th>Users</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($roles as $role)
            <tr>
                <td class="td-mono">{{ method_exists($roles, 'firstItem') ? $roles->firstItem() + $loop->index : $loop->iteration }}</td>
                <td style="font-weight:500;">{{ $role->name }}</td>
                <td class="td-mono">{{ $role->slug }}</td>
                <td>
                    <div class="flex gap-2" style="flex-wrap:wrap;">
                    @foreach($role->permissions->take(5) as $perm)
                        <span class="badge" style="background:var(--bg-overlay);color:var(--text-muted);">{{ $perm->slug }}</span>
                    @endforeach
                    @if($role->permissions->count() > 5)
                        <span class="td-muted" style="font-size:11px;">+{{ $role->permissions->count() - 5 }} more</span>
                    @endif
                    </div>
                </td>
                <td class="td-mono">{{ number_format($role->users_count) }}</td>
                {{-- Actions: edit for all, delete only for non-system roles and only if user is super_admin --}}
                <td>
                    <div class="flex gap-2" style="align-items:center;">
                        @if(\App\Support\Roles\RoleManager::isImmutable($role->slug))
                            <span class="badge" style="background:var(--amber-glow);color:var(--amber);
                                                        font-family:var(--font-mono);">
                                immutable
                            </span>
                        @endif

                        {{-- Edit: all admins can view, but immutable roles need super_admin --}}
                        @if(!(\App\Support\Roles\RoleManager::isImmutable($role->slug)) || auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-ghost btn-sm">Edit</a>
                        @endif

                        {{-- Delete: never for immutable, super_admin only for system roles --}}
                        @if(!in_array($role->slug, \App\Support\Roles\RoleManager::all()))
                            @if(auth()->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete role {{ e($role->name) }}?')">
                                    Delete
                                </button>
                            </form>
                            @endif
                        @else
                            <span class="td-muted" style="font-size:11px;">system role</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="td-muted" style="text-align:center;padding:32px;">No roles found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
