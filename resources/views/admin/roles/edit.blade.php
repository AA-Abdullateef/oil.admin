@extends('layouts.admin')
@section('title', 'Edit role — ' . $role->name)
@section('breadcrumb')
    <a href="{{ route('admin.roles.index') }}" style="color:var(--text-muted);text-decoration:none;">
        Roles
    </a>
     / <strong>{{ $role->name }}</strong>
@endsection

@section('content')
<div style="max-width:640px;">
<form method="POST" action="{{ route('admin.roles.update', $role) }}">
    @csrf @method('PUT')

    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">
            <span class="card-title">Role details</span>
            @if(in_array($role->slug, \App\Support\Roles\RoleManager::all()))
                <span class="badge" style="background:var(--amber-glow);color:var(--amber);">
                    System role
                </span>
            @endif
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $role->name) }}" required>
                @error('name') <div style="color:var(--red);font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" class="form-control"
                       value="{{ $role->slug }}" disabled
                       style="font-family:var(--font-mono);opacity:0.5;cursor:not-allowed;">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Slug cannot be changed after creation.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">
            <span class="card-title">Permissions</span>
            <span style="font-size:11px;font-family:var(--font-mono);color:var(--text-muted);">
                {{ count($assignedPermissions) }} assigned
            </span>
        </div>
        <div class="card-body">
            {{-- Select / deselect all --}}
            <div class="flex gap-2" style="margin-bottom:14px;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleAll(true)">Select all</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleAll(false)">Deselect all</button>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;" id="perm-grid">
            @foreach($permissions as $perm)
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;
                           padding:6px 8px;border-radius:var(--radius);
                           border:1px solid var(--border);transition:border-color 0.15s;"
                   onmouseenter="this.style.borderColor='var(--border-hi)'"
                   onmouseleave="this.style.borderColor='var(--border)'">
                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                    {{ in_array($perm->id, old('permissions', $assignedPermissions)) ? 'checked' : '' }}
                    style="accent-color:var(--amber);">
                <span style="font-size:12px;font-family:var(--font-mono);color:var(--text-muted);">
                    {{ $perm->slug }}
                </span>
            </label>
            @endforeach
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">Cancel</a>

        @if(!in_array($role->slug, \App\Support\Roles\RoleManager::all()))
        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
              style="margin-left:auto;">
            @csrf @method('DELETE')
            <button type="button" class="btn btn-danger"
                    onclick="if(confirm('Delete role {{ e($role->name) }}? This will unassign it from all users.')) this.closest('form').submit()">
                Delete role
            </button>
        </form>
        @endif
    </div>
</form>
</div>

@push('scripts')
<script>
function toggleAll(state) {
    document.querySelectorAll('#perm-grid input[type=checkbox]')
        .forEach(cb => cb.checked = state);
}
</script>
@endpush
@endsection