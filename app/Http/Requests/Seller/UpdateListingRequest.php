<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    /**
     * Route middleware (role:seller) and controller guards (isOwnedBy + isDraft)
     * already enforce authorization. FormRequest::authorize() is not duplicating
     * those concerns.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'       => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string', 'min:20', 'max:5000'],
            'condition'         => ['required', Rule::in(['new', 'used', 'refurbished'])],
            'location'          => ['nullable', 'string', 'max:255'],
            'starting_price'    => ['required', 'numeric', 'min:1'],
            'reserve_price'     => ['nullable', 'numeric', 'min:1'],
            'min_bid_increment' => ['nullable', 'numeric', 'min:1'],
            'buy_now_price'     => ['nullable', 'numeric', 'min:1'],
            'starts_at'         => ['nullable', 'date'],
            'ends_at'           => ['nullable', 'date', 'after:starts_at'],
            'auto_extend'       => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'    => 'Please select a category.',
            'category_id.exists'      => 'The selected category is not valid or is no longer active.',
            'title.required'          => 'A listing title is required.',
            'title.max'               => 'Title may not exceed 255 characters.',
            'description.required'    => 'A description is required.',
            'description.min'         => 'Description must be at least 20 characters.',
            'description.max'         => 'Description may not exceed 5000 characters.',
            'condition.required'      => 'Please select the item condition.',
            'condition.in'            => 'Condition must be new, used, or refurbished.',
            'starting_price.required' => 'A starting price is required.',
            'starting_price.min'      => 'Starting price must be at least 1.',
            'reserve_price.min'       => 'Reserve price must be at least 1.',
            'min_bid_increment.min'   => 'Minimum bid increment must be at least 1.',
            'buy_now_price.min'       => 'Buy now price must be at least 1.',
            'ends_at.after'           => 'End date must be after the start date.',
        ];
    }
}