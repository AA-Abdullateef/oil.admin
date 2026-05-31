@extends('layouts.admin')
@section('title', 'Payment sub-methods')
@section('breadcrumb')
    Finance / <strong>Payment sub-methods</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('admin.sub-methods.create') }}" class="btn btn-primary">+ Add sub-method</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">Sub-methods</span>
        <form method="GET" class="flex gap-2" style="flex-wrap:wrap;">
            <select name="method_id" class="form-control" style="width:180px;">
                <option value="">All methods</option>
                @foreach($methods as $method)
                    <option value="{{ $method->id }}" {{ request('method_id') === $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control" style="width:140px;">
                <option value="">All statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search" style="width:200px;">
            <button class="btn btn-ghost">Filter</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Name</th><th>Method</th><th>Destination</th><th>Status</th><th>References</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($subMethods as $subMethod)
            <tr>
                <td class="td-mono">{{ $subMethods->firstItem() + $loop->index }}</td>
                <td>{{ $subMethod->name }}</td>
                <td><span class="badge">{{ $subMethod->method?->name }}</span></td>
                <td class="td-muted">
                    @if($subMethod->bank_name || $subMethod->account_number)
                        {{ trim(($subMethod->bank_name ?? '') . ' ' . ($subMethod->account_number ?? '')) }}
                    @elseif($subMethod->wallet_address)
                        <span class="text-mono">{{ $subMethod->network }} {{ Str::limit($subMethod->wallet_address, 24) }}</span>
                    @else
                        Not set
                    @endif
                </td>
                <td><span class="badge badge-{{ $subMethod->is_active ? 'active' : 'cancelled' }}">{{ $subMethod->is_active ? 'active' : 'inactive' }}</span></td>
                <td class="td-mono">{{ $subMethod->transactions_count }}</td>
                <td>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.sub-methods.edit', $subMethod) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.sub-methods.destroy', $subMethod) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" {{ $subMethod->transactions_count > 0 ? 'disabled' : '' }} onclick="return confirm('Remove {{ e($subMethod->name) }}?')">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="td-muted" style="text-align:center;padding:32px;">No sub-methods yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($subMethods->hasPages())<div style="padding:0 16px 12px;">{{ $subMethods->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
