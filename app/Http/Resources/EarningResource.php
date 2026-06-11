<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'asset' => [
                'id' => $this->asset?->id,
                'symbol' => $this->asset?->symbol,
                'name' => $this->asset?->name,
                'type' => $this->asset?->type,
            ],

            'schedule_id' => $this->schedule_id,

            'type' => $this->type,

            'amount' => number_format(
                (float) $this->amount,
                8,
                '.',
                ''
            ),

            'status' => $this->status,

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}