<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Route middleware (role:admin) already enforces authorization.
     * FormRequest authorize() is not duplicating that concern.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'A category name is required.',
            'name.max'           => 'Category name may not exceed 255 characters.',
            'description.max'    => 'Description may not exceed 1000 characters.',
            'parent_id.exists'   => 'The selected parent category does not exist.',
        ];
    }
}