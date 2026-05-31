@extends('layouts.admin')
@section('title', 'Withdrawals')
@section('breadcrumb')
    Finance / <strong>Withdrawals</strong>
@endsection

@section('topbar-actions')
<form class="flex gap-2 items-center" method="GET">
    <div class="search-bar"><input type="text" name="search" placeholder="Reference or user..." value="{{ request('search') }}"></div>
    <select name="status" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending ({{ $counts['pending'] }})</option>
        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing ({{ $counts['processing'] }})</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $counts['cancelled'] }})</option>
    </select>
</form>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>User</th><th>Reference</th><th>Asset</th><th>Quantity</th><th>Method</th><th>Destination</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($withdrawals as $w)
            <tr>
                <td class="td-mono">{{ $withdrawals->firstItem() + $loop->index }}</td>
                <td><div>{{ $w->user->name }}</div><div class="td-muted">{{ $w->user->email }}</div></td>
                <td class="td-mono">{{ $w->reference }}</td>
                <td class="td-mono">{{ $w->asset->symbol }}</td>
                <td class="td-mono">{{ number_format($w->quantity, 8) }}</td>
                <td class="td-muted">{{ $w->method?->name ?? '-' }}{{ $w->subMethod ? ' / '.$w->subMethod->name : '' }}</td>
                <td class="td-mono" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $w->wallet_address_or_bank }}</td>
                <td><span class="badge badge-{{ $w->status }}">{{ $w->status }}</span></td>
                <td><a href="{{ route('admin.withdrawals.show', $w) }}" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="9" class="td-muted" style="text-align:center;padding:32px;">No withdrawals found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($withdrawals->hasPages())<div style="padding:0 16px 12px;">{{ $withdrawals->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
