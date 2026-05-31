@extends('layouts.admin')
@section('title', 'Contact messages')
@section('breadcrumb')
    System / <strong>Contact messages</strong>
@endsection

@section('topbar-actions')
<form class="flex gap-2 items-center" method="GET">
    <div class="search-bar">
        <input type="text" name="search" placeholder="Name, email, message..." value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-ghost">Filter</button>
    @if(request('search'))
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-ghost">Reset</a>
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
                    <th>Sender</th>
                    <th>Message</th>
                    <th>Registered user</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($messages as $message)
            <tr>
                <td class="td-mono">{{ $messages->firstItem() + $loop->index }}</td>
                <td>
                    <div style="font-weight:500;">{{ $message->name }}</div>
                    <div class="td-muted">{{ $message->email }}</div>
                </td>
                <td class="td-muted" style="max-width:420px;">
                    {{ \Illuminate\Support\Str::limit($message->message, 140) }}
                </td>
                <td>
                    @if($message->user)
                        <div>{{ $message->user->name }}</div>
                        <div class="td-muted">{{ $message->user->email }}</div>
                    @else
                        <span class="td-muted">Guest</span>
                    @endif
                </td>
                <td class="td-muted">
                    <div>{{ $message->created_at->format('M d, Y H:i') }}</div>
                    <div>{{ $message->created_at->diffForHumans() }}</div>
                </td>
                <td><a href="{{ route('admin.contact-messages.show', $message) }}" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="td-muted" style="text-align:center;padding:32px;">No contact messages found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())<div style="padding:0 16px 12px;">{{ $messages->links('admin.partials.pagination') }}</div>@endif
</div>
@endsection
