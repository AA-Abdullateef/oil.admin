<?php

namespace App\Http\Requests\API\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'country_id'    => ['nullable', 'uuid', 'exists:countries,id'],
            'state_id'      => ['nullable', 'uuid', 'exists:states,id'],
            'address'       => ['nullable', 'string', 'max:500'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];
    }
}