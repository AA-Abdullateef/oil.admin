<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'name' => $this->name,
            'type' => $this->type,
            'current_price' => number_format((float) $this->current_price, 8, '.', ''),
            'price_source' => $this->price_source,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'price_history' => $this->when($request->route('asset') !== null, fn () =>
                $this->dynamicPriceHistory()->map(fn ($history) => [
                    'price' => number_format((float) $history->price, 8, '.', ''),
                    'source' => $history->source,
                    'recorded_at' => $history->recorded_at->toIso8601String(),
                ])
            ),
        ];
    }
}
