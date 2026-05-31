@extends('layouts.admin')
@section('title', 'Balances')
@section('breadcrumb')
    Finance / <strong>Dynamic balances</strong>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>User</th><th>Balances</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
            @php $balances = $balanceService->getAllBalances($user); @endphp
            <tr>
                <td class="td-mono">{{ $users->firstItem() + $loop->index }}</td>
                <td><div>{{ $user->name }}</div><div class="td-muted">{{ $user->email }}</div></td>
                <td>
                    @forelse($balances as $row)
                        <span class="badge" style="background:var(--bg-raised);color:var(--text-primary);margin-right:4px;">{{ $row['asset']->symbol }} {{ number_format($row['quantity'], 4) }}</span>
                    @empty
                        <span class="td-muted">No balances</span>
                    @endforelse
                </td>
                <td><span class="badge badge-{{ $user->status }}">{{ $user->status }}</span></td>
                <td><a href="{{ route('admin.balances.show', $user) }}" class="btn btn-ghost btn-sm">Ledger</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="td-muted" style="text-align:center;padding:32px;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div style="padding:0 16px 12px;">{{ $users->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
