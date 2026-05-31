@extends('layouts.admin')
@section('title', 'Edit permission')
@section('breadcrumb')
    <a href="{{ route('admin.permissions.index') }}" style="color:var(--text-muted);text-decoration:none;">
        Permissions
    </a>
     / <strong>{{ $permission->slug }}</strong>
@endsection

@section('content')
<div style="max-width:520px;">
<form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
    @csrf @method('PUT')
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Edit permission</span></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" class="form-control"
                       value="{{ $permission->slug }}" disabled
                       style="font-family:var(--font-mono);opacity:0.5;cursor:not-allowed;">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Slug is immutable.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $permission->name) }}" required>
                @error('name') <div style="color:var(--red);font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $permission->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-ghost">Cancel</a>
        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
              style="margin-left:auto;">
            @csrf @method('DELETE')
            <button type="button" class="btn btn-danger"
                    onclick="if(confirm('Delete permission {{ e($permission->slug) }}?')) this.closest('form').submit()">
                Delete
            </button>
        </form>
    </div>
</form>
</div>
@endsection