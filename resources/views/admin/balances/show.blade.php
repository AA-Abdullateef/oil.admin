@extends('layouts.admin')
@section('title', 'Balances - ' . $user->name)
@section('breadcrumb')
    <a href="{{ route('admin.balances.index') }}" style="color:var(--text-muted);text-decoration:none;">Balances</a>
     / <strong>{{ $user->name }}</strong>
@endsection

@section('content')
<div class="stat-grid mb-6">
    @forelse($balances as $row)
    <div class="stat-card">
        <div class="stat-label">{{ $row['asset']->symbol }}</div>
        <div class="stat-value">{{ number_format($row['quantity'], 4) }}</div>
        <div class="stat-sub">{{ config('app.currency', '$') }}{{ number_format($row['value'], 2) }}</div>
    </div>
    @empty
    <div class="stat-card"><div class="stat-label">Balances</div><div class="stat-value">0</div><div class="stat-sub">No active ledger activity</div></div>
    @endforelse
</div>
<div class="card">
    <div class="card-header"><span class="card-title">Ledger history</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Reference</th><th>Type</th><th>Direction</th><th>Asset</th><th>Quantity</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td class="td-mono">{{ $transactions->firstItem() + $loop->index }}</td>
                <td class="td-mono">{{ $tx->reference }}</td>
                <td><span class="badge badge-{{ $tx->type }}">{{ $tx->type }}</span></td>
                <td><span class="badge badge-{{ $tx->direction }}">{{ $tx->direction }}</span></td>
                <td class="td-mono">{{ $tx->asset->symbol }}</td>
                <td class="td-mono">{{ number_format($tx->quantity, 8) }}</td>
                <td><span class="badge badge-{{ $tx->status }}">{{ $tx->status }}</span></td>
                <td class="td-muted">{{ $tx->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="td-muted" style="text-align:center;padding:32px;">No ledger rows.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())<div style="padding:0 16px 12px;">{{ $transactions->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
