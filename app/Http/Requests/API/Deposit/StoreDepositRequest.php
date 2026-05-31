<?php

namespace App\Http\Requests\API\Deposit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('deposit_funds');
    }

    public function rules(): array
    {
        $settings = app(\App\Services\SettingService::class);

        return [
            'asset_id' => ['required', 'uuid', 'exists:assets,id'],
            'sub_method_id' => [
                'required_without:method_id',
                'uuid',
                Rule::exists('sub_methods', 'id')->where('is_active', true),
            ],
            'method_id' => ['nullable', 'required_without:sub_method_id', 'uuid', 'exists:methods,id'],
            'amount'         => [
                'required', 'numeric',
                'min:' . $settings->get('min_deposit_amount', 10),
                'max:' . $settings->get('max_deposit_amount', 50000),
            ],
            'proof'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

}
