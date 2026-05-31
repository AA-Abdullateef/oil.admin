<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'method_id' => $this->method_id,
            'method' => $this->whenLoaded('method', fn () => [
                'id' => $this->method?->id,
                'name' => $this->method?->name,
            ]),
            'name' => $this->name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'routing_number' => $this->routing_number,
            'swift_code' => $this->swift_code,
            'iban' => $this->iban,
            'wallet_address' => $this->wallet_address,
            'network' => $this->network,
            'instructions' => $this->instructions,
            'is_active' => $this->is_active,
        ];
    }
}
