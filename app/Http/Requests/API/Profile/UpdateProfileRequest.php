<?php

namespace App\Http\Requests\API\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', Rule::unique('users', 'phone')->ignore($user?->id)],
            'country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'state_id' => ['nullable', 'uuid', 'exists:states,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];
    }
}
