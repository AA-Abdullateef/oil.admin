@extends('layouts.admin')
@section('title', 'Add payment sub-method')
@section('breadcrumb')
    <a href="{{ route('admin.sub-methods.index') }}" style="color:var(--text-muted);text-decoration:none;">Payment sub-methods</a>
     / <strong>Add new</strong>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div style="max-width:760px;">
    <form method="POST" action="{{ route('admin.sub-methods.store') }}">
        @csrf
        @include('admin.sub-methods.form')
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Create sub-method</button>
            <a href="{{ route('admin.sub-methods.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
