<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * The route is protected by auth + check.account.status + verified
     * middleware. Any request that reaches this class belongs to a valid
     * authenticated user. Authorization is handled at the route layer.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],

            // Nullable so a user can clear their username.
            // alpha_dash allows letters, numbers, hyphens, and underscores.
            // The unique rule ignores the current user's own row so they
            // can re-save the form without triggering a false conflict.
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],

            'phone' => ['nullable', 'string', 'max:30'],
            'bio'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}