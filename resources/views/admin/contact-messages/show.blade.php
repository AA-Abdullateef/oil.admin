@extends('layouts.admin')
@section('title', 'Contact message')
@section('breadcrumb')
    <a href="{{ route('admin.contact-messages.index') }}" style="color:var(--text-muted);text-decoration:none;">Contact messages</a>
     / <strong>{{ $contactMessage->name }}</strong>
@endsection

@section('content')
<div class="grid-2" style="align-items:start;">
    <div class="card">
        <div class="card-header"><span class="card-title">Message</span></div>
        <div class="card-body">
            <div style="white-space:pre-wrap;line-height:1.7;">{{ $contactMessage->message }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Sender details</span></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-item-label">Name</div>
                    <div class="detail-item-value">{{ $contactMessage->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Email</div>
                    <div class="detail-item-value">{{ $contactMessage->email }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Registered user</div>
                    <div class="detail-item-value">
                        @if($contactMessage->user)
                            <a href="{{ route('admin.users.show', $contactMessage->user) }}" style="color:var(--amber);text-decoration:none;">{{ $contactMessage->user->name }}</a>
                        @else
                            Guest
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Submitted</div>
                    <div class="detail-item-value mono">{{ $contactMessage->created_at->format('M d, Y H:i') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">IP address</div>
                    <div class="detail-item-value mono">{{ $contactMessage->ip_address ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">User agent</div>
                    <div class="detail-item-value">{{ $contactMessage->user_agent ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
