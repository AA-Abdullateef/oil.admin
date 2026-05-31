@extends('layouts.admin')
@section('title', 'Add asset')
@section('breadcrumb')
    <a href="{{ route('admin.assets.index') }}" style="color:var(--text-muted);text-decoration:none;">Assets</a>
     / <strong>Add new</strong>
@endsection

@section('content')
<div style="max-width:600px;">
<form method="POST" action="{{ route('admin.assets.store') }}">
    @csrf
    <div class="card">
        <div class="card-header"><span class="card-title">Asset details</span></div>
        <div class="card-body">
            <div class="form-group"><label class="form-label">Asset name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="form-group"><label class="form-label">Symbol</label><input type="text" name="symbol" class="form-control" value="{{ old('symbol') }}" required style="font-family:var(--font-mono);text-transform:uppercase;"></div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-control" required>
                    @foreach(['currency','crypto','share','commodity'] as $type)
                        <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Current price</label><input type="number" name="current_price" class="form-control" value="{{ old('current_price') }}" step="0.00000001" min="0"></div>
            <div class="form-group">
                <label class="form-label">Price source</label>
                <select name="price_source" class="form-control">
                    @foreach(['manual','coingecko','alphavantage','finnhub'] as $source)
                        <option value="{{ $source }}" {{ old('price_source', 'manual') === $source ? 'selected' : '' }}>{{ ucfirst($source) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    <div class="flex gap-2" style="margin-top:16px;">
        <button type="submit" class="btn btn-primary">Create asset</button>
        <a href="{{ route('admin.assets.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>
@endsection
