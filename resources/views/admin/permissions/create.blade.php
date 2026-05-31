@extends('layouts.admin')
@section('title', 'New permission')
@section('breadcrumb')
    <a href="{{ route('admin.permissions.index') }}" style="color:var(--text-muted);text-decoration:none;">
        Permissions
    </a>
     / <strong>Create permission</strong>
@endsection

@section('content')
<div style="max-width:520px;">
<form method="POST" action="{{ route('admin.permissions.store') }}">
    @csrf
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Permission details</span></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name') }}"
                       placeholder="e.g. View reports" required>
                @error('name') <div style="color:var(--red);font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug') }}"
                       placeholder="e.g. view_reports" required
                       style="font-family:var(--font-mono);">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Lowercase, underscores only. Cannot be changed after creation.
                </div>
                @error('slug') <div style="color:var(--red);font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control" rows="2"
                          placeholder="What this permission allows…">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Create permission</button>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>

@push('scripts')
<script>
// Auto-generate slug from name
document.querySelector('[name=name]').addEventListener('input', function () {
    const slugField = document.querySelector('[name=slug]');
    if (slugField.dataset.touched) return;
    slugField.value = this.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_|_$/g, '');
});
document.querySelector('[name=slug]').addEventListener('input', function () {
    this.dataset.touched = '1';
});
</script>
@endpush
@endsection