<?php

namespace App\Http\Requests\API\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('withdraw_funds');
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
            'amount' => [
                'required', 'numeric',
                'min:' . $settings->get('min_withdrawal_amount', 20),
                'max:' . $settings->get('max_withdrawal_amount', 20000),
            ],
            'destination_type' => ['required', 'in:bank,crypto'],
            'account_name' => ['nullable', 'required_if:destination_type,bank', 'string', 'max:255'],
            'account_number' => ['nullable', 'required_if:destination_type,bank', 'string', 'max:255'],
            'bank_name' => ['nullable', 'required_if:destination_type,bank', 'string', 'max:255'],
            'wallet_address' => ['nullable', 'required_if:destination_type,crypto', 'string', 'max:500'],
            'network' => ['nullable', 'required_if:destination_type,crypto', 'string', 'max:255'],
        ];
    }

}
