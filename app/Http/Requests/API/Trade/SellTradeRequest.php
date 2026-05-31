<?php

namespace App\Http\Requests\API\Trade;

use Illuminate\Foundation\Http\FormRequest;

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
            'to_asset_id' => ['required', 'uuid', 'exists:assets,id', 'different:from_asset_id'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
        ];
    }
}
