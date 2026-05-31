@extends('layouts.admin')
@section('title', 'KYC — ' . $profile->user->name)
@section('breadcrumb')
    <a href="{{ route('admin.kyc.index') }}" style="color:var(--text-muted);text-decoration:none;">
        KYC
    </a>
     / <strong>{{ $profile->user->name }}</strong>
@endsection

@section('content')
<div class="grid-2" style="align-items:start;gap:20px;">

    {{-- Left: user info + decision --}}
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <span class="card-title">Applicant</span>
                <span class="badge badge-{{ str_replace('_','-',$profile->kyc_status) }}"
                      style="{{ $profile->kyc_status === 'under_review' ? 'background:var(--blue-dim);color:var(--blue);' : '' }}">
                    {{ str_replace('_', ' ', $profile->kyc_status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-item-label">Full name</div>
                        <div class="detail-item-value">{{ $profile->user->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Email</div>
                        <div class="detail-item-value">{{ $profile->user->email }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Date of birth</div>
                        <div class="detail-item-value mono">{{ $profile->date_of_birth?->format('M d, Y') ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Country</div>
                        <div class="detail-item-value">{{ $profile->country?->name ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Document type</div>
                        <div class="detail-item-value">{{ str_replace('_', ' ', $profile->id_document_type ?? '—') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Submitted</div>
                        <div class="detail-item-value mono">{{ $profile->kyc_submitted_at?->format('M d, Y H:i') ?? '—' }}</div>
                    </div>
                    @if($profile->kyc_reviewed_at)
                    <div class="detail-item">
                        <div class="detail-item-label">Reviewed</div>
                        <div class="detail-item-value mono">{{ $profile->kyc_reviewed_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Reviewed by</div>
                        <div class="detail-item-value">{{ $profile->reviewedBy?->name ?? '—' }}</div>
                    </div>
                    @endif
                </div>

                @if($profile->kyc_rejection_reason)
                <div class="divider"></div>
                <div class="detail-item-label" style="margin-bottom:6px;">Previous note</div>
                <div style="background:var(--red-dim);border:1px solid rgba(242,92,92,0.15);
                            border-radius:var(--radius);padding:10px 14px;font-size:13px;
                            color:var(--red);">
                    {{ $profile->kyc_rejection_reason }}
                </div>
                @endif
            </div>
        </div>

        {{-- Decision panel --}}
        @if(in_array($profile->kyc_status, ['submitted', 'under_review']))
        <div class="card">
            <div class="card-header"><span class="card-title">Decision</span></div>
            <div class="card-body">

                {{-- Move to under review --}}
                @if($profile->kyc_status === 'submitted')
                <form method="POST" action="{{ route('admin.kyc.under-review', $profile) }}"
                      style="margin-bottom:16px;">
                    @csrf
                    <button class="btn btn-ghost" style="width:100%;">
                        Mark as under review
                    </button>
                </form>
                @endif

                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.kyc.approve', $profile) }}"
                      style="margin-bottom:12px;">
                    @csrf
                    <button class="btn btn-success" style="width:100%;">
                        ✓ Approve — verify identity
                    </button>
                </form>

                {{-- Reject --}}
                <form method="POST" action="{{ route('admin.kyc.reject', $profile) }}"
                      style="margin-bottom:12px;">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Rejection reason (shown to user)</label>
                        <textarea name="reason" class="form-control" rows="2"
                                  placeholder="Explain what is wrong or missing…" required></textarea>
                    </div>
                    <button class="btn btn-danger" style="width:100%;">✗ Reject</button>
                </form>

                {{-- Request more info --}}
                <form method="POST" action="{{ route('admin.kyc.request-info', $profile) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Request additional info</label>
                        <textarea name="reason" class="form-control" rows="2"
                                  placeholder="What do you need from the user?…" required></textarea>
                    </div>
                    <button class="btn btn-ghost" style="width:100%;">Request more info</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Right: document viewer --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Submitted documents</span></div>
        <div class="card-body">
            @forelse($documents as $field => $url)
            <div style="margin-bottom:20px;">
                <div class="detail-item-label" style="margin-bottom:8px;">
                    {{ ucwords(str_replace('_', ' ', $field)) }}
                </div>
                @php $ext = pathinfo($profile->$field, PATHINFO_EXTENSION); @endphp
                @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                    <img src="{{ $url }}"
                         style="width:100%;border-radius:var(--radius);border:1px solid var(--border);
                                object-fit:cover;max-height:260px;"
                         alt="{{ $field }}">
                @else
                    <a href="{{ $url }}" target="_blank" class="btn btn-ghost" style="width:100%;">
                        View {{ strtoupper($ext) }} document →
                    </a>
                @endif
            </div>
            @empty
            <div class="td-muted" style="text-align:center;padding:32px;">
                No documents submitted yet.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection