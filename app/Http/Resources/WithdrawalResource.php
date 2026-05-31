<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'amount' => number_format((float) $this->amount, 2),
            'quantity' => number_format((float) $this->quantity, 8, '.', ''),
            'asset' => $this->whenLoaded('asset', fn () => [
                'id' => $this->asset->id,
                'symbol' => $this->asset->symbol,
                'name' => $this->asset->name,
            ]),
            'method' => $this->whenLoaded('method', fn () => [
                'id' => $this->method?->id,
                'name' => $this->method?->name,
            ]),
            'sub_method' => $this->whenLoaded('subMethod', fn () => [
                'id' => $this->subMethod?->id,
                'name' => $this->subMethod?->name,
                'method_id' => $this->subMethod?->method_id,
            ]),
            'destination' => $this->whenLoaded('withdrawalProof', fn () => [
                'type' => $this->withdrawalProof?->destination_type,
                'account_name' => $this->withdrawalProof?->account_name,
                'account_number' => $this->withdrawalProof?->account_number,
                'bank_name' => $this->withdrawalProof?->bank_name,
                'wallet_address' => $this->withdrawalProof?->wallet_address,
                'network' => $this->withdrawalProof?->network,
                'proof' => $this->withdrawalProof?->proof,
                'proof_url' => $this->withdrawalProof?->proof
                    ? route('api.v1.withdrawals.proof', $this->id)
                    : null,
            ]),
            'status' => $this->status,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
