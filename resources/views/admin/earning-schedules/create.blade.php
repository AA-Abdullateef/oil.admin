@extends('layouts.admin')
@section('title', 'Add earning schedule')
@section('breadcrumb')
    <a href="{{ route('admin.earning-schedules.index') }}" style="color:var(--text-muted);text-decoration:none;">Earning schedules</a>
     / <strong>Add new</strong>
@endsection

@section('content')
<div style="max-width:620px;">
<form method="POST" action="{{ route('admin.earning-schedules.store') }}">
    @csrf
    <div class="card">
        <div class="card-header"><span class="card-title">Schedule details</span></div>
        <div class="card-body">
            @include('admin.earning-schedules.form', ['schedule' => null])
        </div>
    </div>
    <div class="flex gap-2" style="margin-top:16px;">
        <button type="submit" class="btn btn-primary">Create schedule</button>
        <a href="{{ route('admin.earning-schedules.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>
@endsection
