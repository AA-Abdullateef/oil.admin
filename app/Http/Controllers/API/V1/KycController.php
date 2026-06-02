<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\KycService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function __construct(private readonly KycService $kycService) {}

    public function status(Request $request): JsonResponse
    {
        $profile = $request->user()->profile;

        return $this->success([
            'kyc_status' => $profile?->kyc_status ?? 'pending',
            'kyc_submitted_at' => $profile?->kyc_submitted_at,
            'kyc_reviewed_at' => $profile?->kyc_reviewed_at,
            'kyc_rejection_reason' => $profile?->kyc_rejection_reason,
            'documents' => [
                'id_document_type' => $profile?->id_document_type,
                'id_document_front' => (bool) $profile?->id_document_front,
                'id_document_back' => (bool) $profile?->id_document_back,
                'selfie_with_id' => (bool) $profile?->selfie_with_id,
                'proof_of_address' => (bool) $profile?->proof_of_address,
            ],
        ],
            'KYC status retrieved.'
        );
    }

    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'id_document_type' => ['required', 'in:passport,national_id,drivers_license'],
            'id_document_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_document_back' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie_with_id' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'proof_of_address' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $profile = $request->user()->profile;

        // Prevent re-submission if already verified
        if ($profile?->kyc_status === 'verified') {
            return $this->error('Your identity is already verified.', 422);
        }

        // Prevent re-submission while actively under review
        if ($profile?->kyc_status === 'under_review') {
            return $this->error('Your documents are currently under review. Please wait.', 422);
        }

        $paths = ['id_document_type' => $request->id_document_type];

        foreach (['id_document_front', 'id_document_back', 'selfie_with_id', 'proof_of_address'] as $field) {
            if ($request->hasFile($field)) {
                $paths[$field] = $request->file($field)->store("kyc/{$request->user()->id}", 'private');
            }
        }

        $profile = $this->kycService->submitDocuments($request->user(), $paths);

        return $this->success(
            [
                'kyc_status' => $profile->kyc_status,
                'kyc_submitted_at' => $profile->kyc_submitted_at,
            ],
            'Documents submitted successfully. We will review and notify you within 24 hours.'
        );
    }
}
