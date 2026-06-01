<?php

namespace App\Http\Requests\Admin;

use App\Models\SubMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('manage_settings');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->all())
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all());
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $methodId = $this->input('method_id');

            if (! $methodId) {
                return;
            }

            $this->validateDuplicateIdentifier(
                validator: $validator,
                column: 'account_number',
                label: 'account number'
            );

            $this->validateDuplicateIdentifier(
                validator: $validator,
                column: 'wallet_address',
                label: 'wallet address'
            );

            $this->validateDuplicateIdentifier(
                validator: $validator,
                column: 'iban',
                label: 'IBAN'
            );
        });
    }

    private function validateDuplicateIdentifier(
        Validator $validator,
        string $column,
        string $label
    ): void {
        $value = trim((string) $this->input($column, ''));

        if ($value === '') {
            return;
        }

        $subMethod = $this->route('sub_method');

        $query = SubMethod::query()
            ->where('method_id', $this->input('method_id'))
            ->where($column, $value)
            ->when($subMethod?->id, fn ($query) => $query->whereKeyNot($subMethod->id));

        $exists = $query->exists();

        if ($exists) {
            $validator->errors()->add($column, "A sub-method with this {$label} already exists.");
        }
    }
}
