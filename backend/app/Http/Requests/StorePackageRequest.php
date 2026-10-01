<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'show_price' => 'nullable|boolean',
            'features' => 'nullable|array',
            'is_recommended' => 'nullable|boolean',
            'cta_label' => 'required|string|max:255',
            'cta_action' => 'required|in:order,contact',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
