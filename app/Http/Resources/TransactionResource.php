<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'type'      => $this->type,
            'direction' => $this->direction,

            // quantity: asset units — stored at write time, never changes.
            // This is what the user actually moved (e.g. 10 SHELL, 250 USD).
            'quantity'  => number_format((float) $this->quantity, 8, '.', ''),

            // amount: USD cost = quantity × rate at transaction time.
            // Shown for informational purposes only — not used in balance math.
            'amount'    => number_format((float) $this->amount, 2),

            // rate: asset price in USD when this transaction was created.
            // Allows users to see the exact price at which a trade executed.
            'rate'      => number_format((float) $this->rate, 8, '.', ''),

            'asset' => $this->whenLoaded('asset', fn () => [
                'id'     => $this->asset->id,
                'symbol' => $this->asset->symbol,
                'name'   => $this->asset->name,
                'type'   => $this->asset->type,
            ]),

            'method' => $this->whenLoaded('method', fn () => [
                'id'   => $this->method?->id,
                'name' => $this->method?->name,
            ]),

            'sub_method' => $this->whenLoaded('subMethod', fn () => [
                'id'        => $this->subMethod?->id,
                'name'      => $this->subMethod?->name,
                'method_id' => $this->subMethod?->method_id,
            ]),

            'status'     => $this->status,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
