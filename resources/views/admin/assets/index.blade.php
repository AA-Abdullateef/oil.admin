@extends('layouts.admin')
@section('title', 'Assets')
@section('breadcrumb')
    Market / <strong>Assets</strong>
@endsection

@section('topbar-actions')
    <form method="POST" action="{{ route('admin.assets.sync-prices') }}">
        @csrf
        <button class="btn btn-ghost" onclick="return confirm('Sync current prices from configured APIs now?')">Sync prices</button>
    </form>
    <a href="{{ route('admin.assets.create') }}" class="btn btn-primary">+ Add asset</a>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Asset</th><th>Symbol</th><th>Type</th><th>Current price</th><th>Price source</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($assets as $asset)
            <tr>
                <td class="td-mono">{{ $assets->firstItem() + $loop->index }}</td>
                <td>{{ $asset->name }}</td>
                <td class="td-mono" style="font-weight:500;color:var(--amber);">{{ $asset->symbol }}</td>
                <td><span class="badge">{{ $asset->type }}</span></td>
                <td class="td-mono">{{ config('app.currency', '$') }}{{ number_format($asset->current_price, 8) }}</td>
                <td class="td-muted">{{ $asset->price_source ?? 'manual' }}</td>
                <td><span class="badge badge-{{ $asset->status }}">{{ $asset->status }}</span></td>
                <td>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Remove {{ $asset->name }}?')">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="td-muted" style="text-align:center;padding:32px;">No assets yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($assets->hasPages())<div style="padding:0 16px 12px;">{{ $assets->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
