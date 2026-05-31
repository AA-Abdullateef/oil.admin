@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('breadcrumb')
    System / <strong>Audit logs</strong>
@endsection

@section('topbar-actions')
<form class="flex gap-2 items-center" method="GET" style="flex-wrap:wrap;">
    <div class="search-bar">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        <input type="text" name="search" placeholder="Event, actor, IP, model..." value="{{ request('search') }}">
    </div>
    <select name="event" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">Event</option>
        @foreach($events as $event)
            <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ str_replace('_', ' ', $event) }}</option>
        @endforeach
    </select>
    <select name="actor" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">Actor</option>
        <option value="system" {{ request('actor') === 'system' ? 'selected' : '' }}>System</option>
        @foreach($actors as $actor)
            <option value="{{ $actor->id }}" {{ request('actor') === $actor->id ? 'selected' : '' }}>{{ $actor->name }}</option>
        @endforeach
    </select>
    @if(request()->hasAny(['search', 'event', 'actor']))
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost">Reset</a>
    @endif
</form>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Event</th>
                    <th>Actor</th>
                    <th>Target</th>
                    <th>Changes</th>
                    <th>Metadata</th>
                    <th>IP</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
            @forelse($auditLogs as $log)
                <tr>
                    <td class="td-mono">{{ $auditLogs->firstItem() + $loop->index }}</td>
                    <td>
                        <div style="font-weight:500;">{{ str_replace('_', ' ', $log->event) }}</div>
                        <div class="td-mono" style="font-size:10px;color:var(--text-faint);">{{ substr($log->id, 0, 8) }}</div>
                    </td>
                    <td>
                        <div>{{ $log->user?->name ?? 'System' }}</div>
                        @if($log->user)
                            <div class="td-muted">{{ $log->user->email }}</div>
                        @endif
                    </td>
                    <td>
                        @if($log->auditable_type)
                            <div>{{ class_basename($log->auditable_type) }}</div>
                            <div class="td-mono" style="font-size:10px;">{{ $log->auditable_id ? '#'.substr($log->auditable_id, 0, 8) : '' }}</div>
                        @else
                            <span class="td-muted">-</span>
                        @endif
                    </td>
                    <td class="td-muted" style="min-width:220px;">
                        @if($log->old_values || $log->new_values)
                            <details>
                                <summary style="cursor:pointer;color:var(--text-primary);">View diff</summary>
                                @if($log->old_values)
                                    <div class="td-mono" style="margin-top:8px;color:var(--red);white-space:pre-wrap;">Old: {{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                                @endif
                                @if($log->new_values)
                                    <div class="td-mono" style="margin-top:8px;color:var(--green);white-space:pre-wrap;">New: {{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                                @endif
                            </details>
                        @else
                            -
                        @endif
                    </td>
                    <td class="td-muted" style="min-width:200px;">
                        @if($log->metadata)
                            <details>
                                <summary style="cursor:pointer;color:var(--text-primary);">View</summary>
                                <div class="td-mono" style="margin-top:8px;white-space:pre-wrap;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                            </details>
                        @else
                            -
                        @endif
                    </td>
                    <td class="td-mono">{{ $log->ip_address ?? '-' }}</td>
                    <td class="td-muted">
                        <div>{{ $log->created_at->format('M d, Y H:i') }}</div>
                        <div>{{ $log->created_at->diffForHumans() }}</div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="td-muted" style="text-align:center;padding:32px;">No audit logs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($auditLogs->hasPages())
        <div style="padding:0 16px 12px;">{{ $auditLogs->links('admin.partials.pagination') }}</div>
    @endif
</div>
@endsection
