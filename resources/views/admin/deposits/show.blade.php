@extends('layouts.admin')
@section('title', 'Deposit detail')
@section('breadcrumb')
    <a href="{{ route('admin.deposits.index') }}" style="color:var(--text-muted);text-decoration:none;">Deposits</a>
     / <strong>{{ $deposit->reference }}</strong>
@endsection

@section('content')
<div class="grid-2" style="align-items:start;">
    <div class="card">
        <div class="card-header"><span class="card-title">Deposit details</span><span class="badge badge-{{ $deposit->status }}">{{ $deposit->status }}</span></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-item-label">Reference</div><div class="detail-item-value mono">{{ $deposit->reference }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Asset</div><div class="detail-item-value">{{ $deposit->asset->symbol }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Quantity</div><div class="detail-item-value lg">{{ number_format($deposit->quantity, 8) }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Method</div><div class="detail-item-value">{{ $deposit->method?->name ?? '-' }}{{ $deposit->subMethod ? ' / '.$deposit->subMethod->name : '' }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Submitted</div><div class="detail-item-value mono">{{ $deposit->created_at->format('M d, Y H:i') }}</div></div>
                @if($deposit->updated_by)
                    <div class="detail-item"><div class="detail-item-label">Updated at</div><div class="detail-item-value mono">{{ $deposit->updated_at->format('M d, Y H:i') }}</div></div>
                    <div class="detail-item"><div class="detail-item-label">Updated by</div><div class="detail-item-value">{{ $deposit->updatedBy?->name ?? '-' }}</div></div>
                @endif
            </div>
            @if($deposit->depositProof?->proof)
                <div class="divider"></div>
                <a href="{{ route('admin.deposits.proof', $deposit) }}" target="_blank" class="btn btn-ghost">View proof document</a>
            @endif
            @if($deposit->status === 'pending')
                <div class="divider"></div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.deposits.complete', $deposit) }}">@csrf<button class="btn btn-success">Complete deposit</button></form>
                    <form method="POST" action="{{ route('admin.deposits.cancel', $deposit) }}">@csrf<button class="btn btn-danger">Cancel</button></form>
                </div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">User</span></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-item-label">Name</div><div class="detail-item-value">{{ $deposit->user->name }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Email</div><div class="detail-item-value">{{ $deposit->user->email }}</div></div>
            </div>
            <div class="divider"></div>
            <a href="{{ route('admin.users.show', $deposit->user) }}" class="btn btn-ghost btn-sm">View user profile</a>
        </div>
    </div>
</div>
@endsection
