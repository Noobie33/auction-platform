<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    /**
     * The route is protected by auth + check.account.status + verified
     * + role:admin middleware. Authorization logic (self-update guard,
     * admin-on-admin guard) is handled in the controller where the
     * target User model is available.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:active,suspended,banned'],
        ];
    }
}