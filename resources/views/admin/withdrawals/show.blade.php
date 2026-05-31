@extends('layouts.admin')
@section('title', 'Withdrawal ' . $withdrawal->reference)
@section('breadcrumb')
    <a href="{{ route('admin.withdrawals.index') }}" style="color:var(--text-muted);text-decoration:none;">Withdrawals</a>
     / <strong>{{ $withdrawal->reference }}</strong>
@endsection

@section('content')
<div class="grid-2" style="align-items:start;">
    <div class="card">
        <div class="card-header"><span class="card-title">Withdrawal summary</span><span class="badge badge-{{ $withdrawal->status }}">{{ $withdrawal->status }}</span></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-item-label">Reference</div><div class="detail-item-value mono">{{ $withdrawal->reference }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Asset</div><div class="detail-item-value">{{ $withdrawal->asset->symbol }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Quantity</div><div class="detail-item-value lg">{{ number_format($withdrawal->quantity, 8) }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Method</div><div class="detail-item-value">{{ $withdrawal->method?->name ?? '-' }}{{ $withdrawal->subMethod ? ' / '.$withdrawal->subMethod->name : '' }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Destination</div><div class="detail-item-value mono">{{ $withdrawal->wallet_address_or_bank }}</div></div>
                <div class="detail-item"><div class="detail-item-label">Requested</div><div class="detail-item-value mono">{{ $withdrawal->created_at->format('M d, Y H:i') }}</div></div>
            </div>
            @if($withdrawal->payment_evidence)
                <div class="divider"></div>
                <a href="{{ route('admin.withdrawals.proof', $withdrawal) }}" target="_blank" class="btn btn-ghost">View payout proof</a>
            @endif
        </div>
    </div>
    @if($withdrawal->status === 'pending')
    <div class="card">
        <div class="card-header"><span class="card-title">Review withdrawal</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.withdrawals.process', $withdrawal) }}" enctype="multipart/form-data" style="margin-bottom:20px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Admin notes</label>
                    <textarea name="admin_notes" class="form-control" rows="3">{{ old('admin_notes') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Payout proof</label>
                    <input type="file" name="payment_evidence" class="form-control" required>
                </div>
                <button class="btn btn-success">Process withdrawal</button>
            </form>
            <form method="POST" action="{{ route('admin.withdrawals.cancel', $withdrawal) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Cancel reason</label>
                    <textarea name="reason" class="form-control" rows="3" required minlength="10">{{ old('reason') }}</textarea>
                </div>
                <button class="btn btn-danger">Cancel withdrawal</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
