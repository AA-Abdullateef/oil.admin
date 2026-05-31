<?php

namespace App\Services;

use App\Exceptions\KycNotVerifiedException;
use App\Models\Profile;
use App\Models\User;

class KycService
{
    public function isVerified(User $user): bool
    {
        return $user->profile?->kyc_status === 'verified';
    }

    public function assertVerified(User $user): void
    {
        if (! $this->isVerified($user)) {
            throw new KycNotVerifiedException(
                $this->friendlyMessage($user->profile?->kyc_status ?? 'pending')
            );
        }
    }

    public function submitDocuments(User $user, array $paths): Profile
    {
        $profile = $user->profile ?? $user->profile()->create();

        $profile->update([
            'kyc_status'        => 'submitted',
            'kyc_submitted_at'  => now(),
            'id_document_type'  => $paths['id_document_type'] ?? $profile->id_document_type,
            'id_document_front' => $paths['id_document_front'] ?? $profile->id_document_front,
            'id_document_back'  => $paths['id_document_back']  ?? $profile->id_document_back,
            'selfie_with_id'    => $paths['selfie_with_id']    ?? $profile->selfie_with_id,
            'proof_of_address'  => $paths['proof_of_address']  ?? $profile->proof_of_address,
        ]);

        return $profile;
    }

    public function approve(Profile $profile, User $admin): Profile
    {
        $profile->update([
            'kyc_status'          => 'verified',
            'kyc_reviewed_at'     => now(),
            'kyc_reviewed_by'     => $admin->id,
            'kyc_rejection_reason'=> null,
        ]);

        return $profile;
    }

    public function reject(Profile $profile, User $admin, string $reason): Profile
    {
        $profile->update([
            'kyc_status'          => 'rejected',
            'kyc_reviewed_at'     => now(),
            'kyc_reviewed_by'     => $admin->id,
            'kyc_rejection_reason'=> $reason,
        ]);

        return $profile;
    }

    public function requestMoreInfo(Profile $profile, User $admin, string $reason): Profile
    {
        $profile->update([
            'kyc_status'          => 'pending',
            'kyc_reviewed_at'     => now(),
            'kyc_reviewed_by'     => $admin->id,
            'kyc_rejection_reason'=> $reason,
        ]);

        return $profile;
    }

    private function friendlyMessage(string $status): string
    {
        return match ($status) {
            'pending'      => 'Your identity has not been verified yet. Please submit your KYC documents.',
            'submitted'    => 'Your documents are under review. You will be notified once approved.',
            'under_review' => 'Your documents are currently being reviewed by our team.',
            'rejected'     => 'Your KYC was rejected. Please re-submit with correct documents.',
            default        => 'Identity verification is required before you can perform this action.',
        };
    }
}