<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\KycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KycController extends Controller
{
    public function __construct(private readonly KycService $kycService) {}

    public function index(Request $request): View
    {
        $profiles = Profile::with('user')
            ->whereIn('kyc_status', ['submitted', 'under_review'])
            ->when($request->status, fn ($q) => $q->where('kyc_status', $request->status))
            ->latest('kyc_submitted_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'submitted'    => Profile::where('kyc_status', 'submitted')->count(),
            'under_review' => Profile::where('kyc_status', 'under_review')->count(),
            'verified'     => Profile::where('kyc_status', 'verified')->count(),
            'rejected'     => Profile::where('kyc_status', 'rejected')->count(),
        ];

        return view('admin.kyc.index', compact('profiles', 'counts'));
    }

    public function show(Profile $profile): View
    {
        $profile->load(['user', 'reviewedBy']);

        // Generate temporary signed URLs for private documents
        $documents = [];
        foreach (['id_document_front', 'id_document_back', 'selfie_with_id', 'proof_of_address'] as $field) {
            if ($profile->$field) {
                $documents[$field] = Storage::disk('private')->temporaryUrl(
                    $profile->$field,
                    now()->addMinutes(30)
                );
            }
        }

        return view('admin.kyc.show', compact('profile', 'documents'));
    }

    public function approve(Profile $profile): RedirectResponse
    {
        if (! in_array($profile->kyc_status, ['submitted', 'under_review'])) {
            return back()->with('error', 'Only submitted or under-review applications can be approved.');
        }

        $this->kycService->approve($profile, auth()->user());

        return back()->with('success', "KYC approved for {$profile->user->name}.");
    }

    public function reject(Request $request, Profile $profile): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        if (! in_array($profile->kyc_status, ['submitted', 'under_review'])) {
            return back()->with('error', 'Only submitted or under-review applications can be rejected.');
        }

        $this->kycService->reject($profile, auth()->user(), $request->reason);

        return back()->with('success', "KYC rejected for {$profile->user->name}.");
    }

    public function requestInfo(Request $request, Profile $profile): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $this->kycService->requestMoreInfo($profile, auth()->user(), $request->reason);

        return back()->with('success', "Additional info requested from {$profile->user->name}.");
    }

    public function markUnderReview(Profile $profile): RedirectResponse
    {
        if ($profile->kyc_status !== 'submitted') {
            return back()->with('error', 'Only submitted applications can be moved to under-review.');
        }

        $profile->update(['kyc_status' => 'under_review']);

        return back()->with('success', 'Application marked as under review.');
    }
}