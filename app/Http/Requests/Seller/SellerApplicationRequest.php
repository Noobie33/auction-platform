<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class SellerApplicationRequest extends FormRequest
{
    /**
     * The route is protected by auth + check.account.status + verified
     * + role:bidder middleware. Any request that reaches this class
     * belongs to an active, verified bidder.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivation' => ['required', 'string', 'min:50', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivation.min' => 'Please write at least 50 characters explaining your motivation.',
            'motivation.max' => 'Your motivation may not exceed 2000 characters.',
        ];
    }
}