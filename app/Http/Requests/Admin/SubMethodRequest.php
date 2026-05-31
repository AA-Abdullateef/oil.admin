<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('manage_settings');
    }

    public function rules(): array
    {
        $subMethod = $this->route('sub_method');

        return [
            'method_id' => ['required', 'uuid', 'exists:methods,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_methods')
                    ->where('method_id', $this->input('method_id'))
                    ->ignore($subMethod?->id),
            ],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'routing_number' => ['nullable', 'string', 'max:255'],
            'swift_code' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:255'],
            'wallet_address' => ['nullable', 'string', 'max:500'],
            'network' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
