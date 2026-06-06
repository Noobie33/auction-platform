<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'image'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}