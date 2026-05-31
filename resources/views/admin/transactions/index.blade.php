@extends('layouts.admin')
@section('title', 'Transactions')
@section('breadcrumb')
    Finance / <strong>Ledger</strong>
@endsection

@section('topbar-actions')
<form class="flex gap-2 items-center" method="GET" style="flex-wrap:wrap;">
    <div class="search-bar"><input type="text" name="search" placeholder="Reference or user..." value="{{ request('search') }}"></div>
    <select name="type" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">Type</option>
        @foreach(['deposit','withdrawal','buy','sell'] as $type)
            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
        @endforeach
    </select>
    <select name="direction" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">Direction</option>
        <option value="credit" {{ request('direction') === 'credit' ? 'selected' : '' }}>Credit</option>
        <option value="debit" {{ request('direction') === 'debit' ? 'selected' : '' }}>Debit</option>
    </select>
</form>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Reference</th><th>User</th><th>Type</th><th>Direction</th><th>Asset</th><th>Quantity</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td class="td-mono">{{ $transactions->firstItem() + $loop->index }}</td>
                <td class="td-mono">{{ $tx->reference }}</td>
                <td><div>{{ $tx->user->name }}</div><div class="td-muted">{{ $tx->user->email }}</div></td>
                <td><span class="badge badge-{{ $tx->type }}">{{ $tx->type }}</span></td>
                <td><span class="badge badge-{{ $tx->direction }}">{{ $tx->direction }}</span></td>
                <td class="td-mono">{{ $tx->asset->symbol }}</td>
                <td class="td-mono" style="color:{{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }};">{{ $tx->direction === 'credit' ? '+' : '-' }}{{ number_format($tx->quantity, 8) }}</td>
                <td><span class="badge badge-{{ $tx->status }}">{{ $tx->status }}</span></td>
                <td class="td-muted">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                <td><a href="{{ route('admin.transactions.show', $tx) }}" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="10" class="td-muted" style="text-align:center;padding:32px;">No transactions found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())<div style="padding:0 16px 12px;">{{ $transactions->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
