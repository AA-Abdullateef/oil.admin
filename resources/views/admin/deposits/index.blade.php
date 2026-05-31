@extends('layouts.admin')
@section('title', 'Deposits')
@section('breadcrumb')
    Finance / <strong>Deposits</strong>
@endsection

@section('topbar-actions')
<form class="flex gap-2 items-center" method="GET">
    <div class="search-bar">
        <input type="text" name="search" placeholder="Reference or user..." value="{{ request('search') }}">
    </div>
    <select name="status" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending ({{ $counts['pending'] }})</option>
        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed ({{ $counts['completed'] }})</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $counts['cancelled'] }})</option>
    </select>
</form>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>User</th><th>Reference</th><th>Asset</th><th>Quantity</th><th>Method</th><th>Proof</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($deposits as $dep)
            <tr>
                <td class="td-mono">{{ $deposits->firstItem() + $loop->index }}</td>
                <td><div>{{ $dep->user->name }}</div><div class="td-muted">{{ $dep->user->email }}</div></td>
                <td class="td-mono">{{ $dep->reference }}</td>
                <td class="td-mono">{{ $dep->asset->symbol }}</td>
                <td class="td-mono">{{ number_format($dep->quantity, 8) }}</td>
                <td class="td-muted">{{ $dep->method?->name ?? '-' }}{{ $dep->subMethod ? ' / '.$dep->subMethod->name : '' }}</td>
                <td>
                    @if($dep->depositProof?->proof)
                        <a href="{{ route('admin.deposits.proof', $dep) }}" target="_blank" class="btn btn-ghost btn-sm">View</a>
                    @else
                        <span class="td-muted">-</span>
                    @endif
                </td>
                <td><span class="badge badge-{{ $dep->status }}">{{ $dep->status }}</span></td>
                <td>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.deposits.show', $dep) }}" class="btn btn-ghost btn-sm">View</a>
                        @if($dep->status === 'pending')
                            <form method="POST" action="{{ route('admin.deposits.complete', $dep) }}">@csrf<button class="btn btn-success btn-sm">Complete</button></form>
                            <form method="POST" action="{{ route('admin.deposits.cancel', $dep) }}">@csrf<button class="btn btn-danger btn-sm">Cancel</button></form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="td-muted" style="text-align:center;padding:32px;">No deposits found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($deposits->hasPages())<div style="padding:0 16px 12px;">{{ $deposits->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
