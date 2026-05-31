<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}