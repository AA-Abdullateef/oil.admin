@extends('layouts.admin')
@section('title', 'Edit ' . $subMethod->name)
@section('breadcrumb')
    <a href="{{ route('admin.sub-methods.index') }}" style="color:var(--text-muted);text-decoration:none;">Payment sub-methods</a>
     / <strong>{{ $subMethod->name }}</strong>
@endsection

@section('content')
<div style="max-width:760px;">
    <form method="POST" action="{{ route('admin.sub-methods.update', $subMethod) }}">
        @csrf @method('PUT')
        @include('admin.sub-methods.form')
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.sub-methods.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="button" class="btn btn-danger" style="margin-left:auto;" {{ $subMethod->transactions()->exists() ? 'disabled' : '' }} onclick="if(confirm('Remove {{ e($subMethod->name) }}?')) document.getElementById('delete-form').submit()">Remove</button>
        </div>
    </form>
    <form id="delete-form" method="POST" action="{{ route('admin.sub-methods.destroy', $subMethod) }}" style="display:none;">@csrf @method('DELETE')</form>
</div>
@endsection
