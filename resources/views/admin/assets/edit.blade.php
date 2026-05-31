@extends('layouts.admin')
@section('title', 'Edit ' . $asset->name)
@section('breadcrumb')
    <a href="{{ route('admin.assets.index') }}" style="color:var(--text-muted);text-decoration:none;">Assets</a>
     / <strong>{{ $asset->name }}</strong>
@endsection

@section('content')
<div style="max-width:600px;">
    <form method="POST" action="{{ route('admin.assets.update', $asset) }}">
        @csrf @method('PUT')
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><span class="card-title">Edit asset</span><span class="badge badge-{{ $asset->status }}">{{ $asset->status }}</span></div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Asset name</label><input type="text" name="name" class="form-control" value="{{ old('name', $asset->name) }}" required></div>
                <div class="form-group"><label class="form-label">Symbol</label><input type="text" name="symbol" class="form-control" value="{{ old('symbol', $asset->symbol) }}" required style="font-family:var(--font-mono);text-transform:uppercase;"></div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control" required>
                        @foreach(['currency','crypto','share','commodity'] as $type)
                            <option value="{{ $type }}" {{ old('type', $asset->type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Current price</label><input type="number" name="current_price" class="form-control" value="{{ old('current_price', $asset->current_price) }}" step="0.00000001" min="0"></div>
                <div class="form-group">
                    <label class="form-label">Price source</label>
                    <select name="price_source" class="form-control">
                        @foreach(['manual','coingecko','alphavantage','finnhub'] as $source)
                            <option value="{{ $source }}" {{ old('price_source', $asset->price_source ?? 'manual') === $source ? 'selected' : '' }}>{{ ucfirst($source) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active" {{ old('status', $asset->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $asset->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.assets.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="button" class="btn btn-danger" style="margin-left:auto;" onclick="if(confirm('Remove {{ e($asset->name) }} permanently?')) document.getElementById('delete-form').submit()">Remove asset</button>
        </div>
    </form>
    <form id="delete-form" method="POST" action="{{ route('admin.assets.destroy', $asset) }}" style="display:none;">@csrf @method('DELETE')</form>
</div>
@endsection
