<?php

namespace App\Http\Requests\API\Trade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellTradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('sell_assets');
    }

    public function rules(): array
    {
        return [
            'from_asset_id' => ['required', 'uuid', 'exists:assets,id'],

            // to_asset must be a currency or crypto — the asset being received.
            // Remove the whereIn line below to allow any asset type as the destination.
            'to_asset_id'   => ['required', 'uuid', Rule::exists('assets', 'id')->whereIn('type', ['currency', 'crypto'])->where('status', 'active'), 'different:from_asset_id'],

            'amount'        => ['required', 'numeric', 'min:0.00000001'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_asset_id.exists' => 'The destination asset must be a currency or crypto asset.',
        ];
    }
}