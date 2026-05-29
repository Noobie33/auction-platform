<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Public endpoint — any visitor may attempt to register.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:100'],

            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique('users', 'username'),
            ],

            'email'    => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],

            'phone'    => ['nullable', 'string', 'min:7', 'max:20'],
        ];
    }
}