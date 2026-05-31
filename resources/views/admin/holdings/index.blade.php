@extends('layouts.admin')
@section('title', 'Holdings')
@section('breadcrumb')
    Market / <strong>Dynamic holdings</strong>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>User</th><th>Asset positions</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($users as $user)
            @php $balances = $balanceService->getAllBalances($user); @endphp
            <tr>
                <td class="td-mono">{{ $users->firstItem() + $loop->index }}</td>
                <td><div>{{ $user->name }}</div><div class="td-muted">{{ $user->email }}</div></td>
                <td>
                    @forelse($balances as $row)
                        <div style="margin-bottom:6px;">
                            <span class="td-mono" style="color:var(--amber);">{{ $row['asset']->symbol }}</span>
                            <span class="td-muted">{{ number_format($row['quantity'], 8) }} units, {{ config('app.currency', '$') }}{{ number_format($row['value'], 2) }}</span>
                        </div>
                    @empty
                        <span class="td-muted">No positions</span>
                    @endforelse
                </td>
                <td><span class="badge badge-{{ $user->status }}">{{ $user->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="4" class="td-muted" style="text-align:center;padding:32px;">No holding data yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div style="padding:0 16px 12px;">{{ $users->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
