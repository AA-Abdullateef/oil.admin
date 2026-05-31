@extends('layouts.admin')
@section('title', 'KYC Reviews')
@section('breadcrumb')
    User management / <strong>KYC reviews</strong>
@endsection
@section('topbar-actions')
<form class="flex gap-2 items-center" method="GET">
    <select name="status" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
        <option value="">Pending queue</option>
        <option value="submitted"    {{ request('status') === 'submitted'    ? 'selected' : '' }}>Submitted</option>
        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under review</option>
        <option value="verified"     {{ request('status') === 'verified'     ? 'selected' : '' }}>Verified</option>
        <option value="rejected"     {{ request('status') === 'rejected'     ? 'selected' : '' }}>Rejected</option>
    </select>
</form>
@endsection

@section('content')
<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-label">Awaiting review</div>
        <div class="stat-value {{ ($counts['submitted'] + $counts['under_review']) > 0 ? 'amber' : '' }}">
            {{ $counts['submitted'] + $counts['under_review'] }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Submitted</div>
        <div class="stat-value">{{ $counts['submitted'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Under review</div>
        <div class="stat-value">{{ $counts['under_review'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Verified</div>
        <div class="stat-value green">{{ $counts['verified'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rejected</div>
        <div class="stat-value">{{ $counts['rejected'] }}</div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>User</th>
                    <th>Document type</th>
                    <th>Documents</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($profiles as $profile)
            <tr>
                <td class="td-mono">{{ $profiles->firstItem() + $loop->index }}</td>
                <td>
                    <div style="font-weight:500;">{{ $profile->user->name }}</div>
                    <div class="td-muted">{{ $profile->user->email }}</div>
                </td>
                <td class="td-muted">{{ str_replace('_', ' ', $profile->id_document_type ?? '—') }}</td>
                <td>
                    <div class="flex gap-2" style="flex-wrap:wrap;">
                        @foreach(['id_document_front' => 'Front', 'id_document_back' => 'Back', 'selfie_with_id' => 'Selfie', 'proof_of_address' => 'Address'] as $field => $label)
                            @if($profile->$field)
                                <span class="badge badge-active">{{ $label }}</span>
                            @else
                                <span class="badge" style="background:var(--bg-overlay);color:var(--text-faint);">{{ $label }}</span>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td class="td-muted">{{ $profile->kyc_submitted_at?->diffForHumans() ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ str_replace('_', '-', $profile->kyc_status) }}"
                          style="{{ $profile->kyc_status === 'under_review' ? 'background:var(--blue-dim);color:var(--blue);' : '' }}">
                        {{ str_replace('_', ' ', $profile->kyc_status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.kyc.show', $profile) }}" class="btn btn-primary btn-sm">Review</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="td-muted" style="text-align:center;padding:32px;">
                    No KYC applications in this queue.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($profiles->hasPages())
    <div style="padding:0 16px 12px;">{{ $profiles->links('admin.partials.pagination') }}</div>
    @endif
</div>
@endsection
