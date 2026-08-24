<?php

namespace App\Http\Requests\API\Trade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyTradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('buy_assets');
    }

    public function rules(): array
    {
        return [
            // from_asset must be a currency or crypto — the asset being spent.
            // Remove the whereIn line below to allow any asset type as the source.
            'from_asset_id' => ['required', 'uuid', Rule::exists('assets', 'id')->whereIn('type', ['currency', 'crypto'])->where('status', 'active')],

            'to_asset_id'   => ['required', 'uuid', 'exists:assets,id', 'different:from_asset_id'],
            'amount'        => ['required', 'numeric', 'min:0.00000001'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_asset_id.exists' => 'The source asset must be a currency or crypto asset.',
        ];
    }
}