@extends('layouts.admin')
@section('title', 'Permissions')
@section('breadcrumb')
    Access / <strong>Permissions</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">+ New permission</a>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Slug</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Assigned to roles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($permissions as $perm)
            <tr>
                <td class="td-mono">{{ $permissions->firstItem() + $loop->index }}</td>
                <td class="td-mono" style="color:var(--amber);">{{ $perm->slug }}</td>
                <td style="font-weight:500;">{{ $perm->name }}</td>
                <td class="td-muted">{{ $perm->description ?? '—' }}</td>
                <td class="td-mono">{{ number_format($perm->roles_count) }}</td>
                <td>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.permissions.edit', $perm) }}"
                           class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete permission {{ $perm->slug }}?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="td-muted"
                    style="text-align:center;padding:32px;">No permissions found.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($permissions->hasPages())
    <div style="padding:0 16px 12px;">
        {{ $permissions->links('admin.partials.pagination') }}
    </div>
    @endif
</div>
@endsection
