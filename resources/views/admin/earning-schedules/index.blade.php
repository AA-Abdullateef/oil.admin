@extends('layouts.admin')
@section('title', 'Earning schedules')
@section('breadcrumb')
    Finance / <strong>Earning schedules</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('admin.earning-schedules.create') }}" class="btn btn-primary">+ Add schedule</a>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>S/N</th><th>Asset</th><th>Percentage</th><th>Frequency</th><th>Next run</th><th>Last run</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($schedules as $schedule)
            <tr>
                <td class="td-mono">{{ $schedules->firstItem() + $loop->index }}</td>
                <td><div>{{ $schedule->asset->name }}</div><div class="td-muted">{{ $schedule->asset->symbol }}</div></td>
                <td class="td-mono">{{ number_format($schedule->percentage, 4) }}%</td>
                <td><span class="badge">{{ $schedule->frequency }}</span></td>
                <td class="td-muted">{{ $schedule->next_run_at?->format('M d, Y H:i') }}</td>
                <td class="td-muted">{{ $schedule->last_run_at?->format('M d, Y H:i') ?? '-' }}</td>
                <td><span class="badge badge-{{ $schedule->status }}">{{ $schedule->status }}</span></td>
                <td>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.earning-schedules.edit', $schedule) }}" class="btn btn-ghost btn-sm">Edit</a>
                        @if($schedule->status === 'active')
                            <form method="POST" action="{{ route('admin.earning-schedules.pause', $schedule) }}">@csrf<button class="btn btn-ghost btn-sm">Pause</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.earning-schedules.resume', $schedule) }}">@csrf<button class="btn btn-success btn-sm">Resume</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.earning-schedules.destroy', $schedule) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this earning schedule?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="td-muted" style="text-align:center;padding:32px;">No earning schedules yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($schedules->hasPages())<div style="padding:0 16px 12px;">{{ $schedules->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
