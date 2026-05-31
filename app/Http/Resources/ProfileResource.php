<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'country'       => $this->whenLoaded('country', fn () => [
                'id'   => $this->country->id,
                'name' => $this->country->name,
            ]),
            'state'         => $this->whenLoaded('state', fn () => [
                'id'   => $this->state->id,
                'name' => $this->state->name,
            ]),
            'address'       => $this->address,
            'gender'        => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'kyc_status'    => $this->kyc_status,
        ];
    }
}