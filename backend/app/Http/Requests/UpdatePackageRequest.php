<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'tagline' => 'sometimes|nullable|string|max:255',
            'price' => 'sometimes|nullable|numeric',
            'show_price' => 'sometimes|nullable|boolean',
            'features' => 'sometimes|nullable|array',
            'is_recommended' => 'sometimes|nullable|boolean',
            'cta_label' => 'sometimes|string|max:255',
            'cta_action' => 'sometimes|in:order,contact',
            'sort_order' => 'sometimes|nullable|integer',
            'is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
