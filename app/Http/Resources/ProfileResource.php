<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'country' => $this->whenLoaded('country', fn () => [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'slug' => $this->country->slug,
            ]),
            'state' => $this->whenLoaded('state', fn () => [
                'id' => $this->state->id,
                'name' => $this->state->name,
                'slug' => $this->state->slug,
                'country_id' => $this->state->country_id,
            ]),
            'address' => $this->address,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'kyc_status' => $this->kyc_status,
            'kyc_submitted_at' => $this->kyc_submitted_at,
            'kyc_reviewed_at' => $this->kyc_reviewed_at,
            'kyc_reviewed_by' => $this->kyc_reviewed_by,
            'kyc_rejection_reason' => $this->kyc_rejection_reason,
            'id_document_type' => $this->id_document_type,
            'id_document_front' => $this->id_document_front,
            'id_document_back' => $this->id_document_back,
            'selfie_with_id' => $this->selfie_with_id,
            'proof_of_address' => $this->proof_of_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
