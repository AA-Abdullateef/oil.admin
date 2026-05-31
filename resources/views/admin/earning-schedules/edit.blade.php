@extends('layouts.admin')
@section('title', 'Edit earning schedule')
@section('breadcrumb')
    <a href="{{ route('admin.earning-schedules.index') }}" style="color:var(--text-muted);text-decoration:none;">Earning schedules</a>
     / <strong>Edit</strong>
@endsection

@section('content')
<div style="max-width:620px;">
<form method="POST" action="{{ route('admin.earning-schedules.update', $earningSchedule) }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-header"><span class="card-title">Schedule details</span></div>
        <div class="card-body">
            @include('admin.earning-schedules.form', ['schedule' => $earningSchedule])
        </div>
    </div>
    <div class="flex gap-2" style="margin-top:16px;">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('admin.earning-schedules.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>
@endsection
