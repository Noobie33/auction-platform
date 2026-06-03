<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Route middleware (role:admin) already enforces authorization.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id'   => [
                'nullable',
                'exists:categories,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    if ($value !== null && (int) $value === $category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'A category name is required.',
            'name.max'         => 'Category name may not exceed 255 characters.',
            'description.max'  => 'Description may not exceed 1000 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ];
    }
}