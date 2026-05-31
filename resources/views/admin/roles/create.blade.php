@extends('layouts.admin')
@section('title', 'Create role')
@section('breadcrumb')
    <a href="{{ route('admin.roles.index') }}" style="color:var(--text-muted);text-decoration:none;">
        Roles
    </a>
     / <strong>Create role</strong>
@endsection

@section('content')
<div style="max-width:640px;">
<form method="POST" action="{{ route('admin.roles.store') }}">
    @csrf
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Role details</span></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Support Agent" required>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="e.g. support_agent" required style="font-family:var(--font-mono);">
            </div>
            <div class="form-group">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control" rows="2" placeholder="What this role can do…">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Permissions</span></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            @foreach($permissions as $perm)
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:6px 8px;border-radius:var(--radius);border:1px solid var(--border);transition:border-color 0.15s;">
                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                    {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}
                    style="accent-color:var(--amber);">
                <span style="font-size:12px;font-family:var(--font-mono);color:var(--text-muted);">{{ $perm->slug }}</span>
            </label>
            @endforeach
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Create role</button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>
@endsection