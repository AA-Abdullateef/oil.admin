<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'reference' => $this->reference,

            // quantity: asset units deposited — stored at write time.
            'quantity'  => number_format((float) $this->quantity, 8, '.', ''),

            // amount: USD equivalent at the time of deposit.
            'amount'    => number_format((float) $this->amount, 2),

            // rate: asset price when deposit was submitted.
            'rate'      => number_format((float) $this->rate, 8, '.', ''),

            'asset' => $this->whenLoaded('asset', fn () => [
                'id'     => $this->asset->id,
                'symbol' => $this->asset->symbol,
                'name'   => $this->asset->name,
            ]),

            'method' => $this->whenLoaded('method', fn () => [
                'id'   => $this->method?->id,
                'name' => $this->method?->name,
            ]),

            'sub_method' => $this->whenLoaded('subMethod', fn () => [
                'id'           => $this->subMethod?->id,
                'name'         => $this->subMethod?->name,
                'method_id'    => $this->subMethod?->method_id,
                'instructions' => $this->subMethod?->instructions,
            ]),

            'proof'     => $this->depositProof?->proof,
            'proof_url' => $this->depositProof?->proof
                ? route('api.v1.deposits.proof', $this->id)
                : null,

            'status'     => $this->status,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}