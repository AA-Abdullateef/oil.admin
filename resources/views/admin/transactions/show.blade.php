@extends('layouts.admin')
@section('title', 'Transaction ' . $transaction->reference)
@section('breadcrumb')
    <a href="{{ route('admin.transactions.index') }}" style="color:var(--text-muted);text-decoration:none;">Transactions</a>
     / <strong>{{ $transaction->reference }}</strong>
@endsection

@section('content')
<div class="grid-2" style="align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Ledger row</span>
            <div class="flex gap-2"><span class="badge badge-{{ $transaction->type }}">{{ $transaction->type }}</span><span class="badge badge-{{ $transaction->direction }}">{{ $transaction->direction }}</span><span class="badge badge-{{ $transaction->status }}">{{ $transaction->status }}</span></div>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-item-label">Reference</div><div class="detail-item-value mono">{{ $transaction->reference }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Asset</div><div class="detail-item-value">{{ $transaction->asset->symbol }} - {{ $transaction->asset->name }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Quantity</div><div class="detail-item-value lg">{{ number_format($transaction->quantity, 8) }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Rate</div><div class="detail-item-value mono">{{ config('app.currency', '$') }}{{ number_format($transaction->rate, 8) }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Amount</div><div class="detail-item-value mono">{{ config('app.currency', '$') }}{{ number_format($transaction->amount, 8) }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Created</div><div class="detail-item-value mono">{{ $transaction->created_at->format('M d, Y H:i:s') }}</div></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">User</span></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-item-label">Name</div><div class="detail-item-value">{{ $transaction->user->name }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Email</div><div class="detail-item-value">{{ $transaction->user->email }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Method</div><div class="detail-item-value">{{ $transaction->method?->name ?? '-' }}{{ $transaction->subMethod ? ' / '.$transaction->subMethod->name : '' }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Updated by</div><div class="detail-item-value">{{ $transaction->updatedBy?->name ?? '-' }}</div></div>
            </div>
            <div class="divider"></div>
            <a href="{{ route('admin.users.show', $transaction->user) }}" class="btn btn-ghost btn-sm">View user</a>
        </div>
    </div>
</div>
@endsection
