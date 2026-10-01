<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'image_url' => 'sometimes|string|max:500',
            'description' => 'sometimes|nullable|string',
            'client_name' => 'sometimes|nullable|string|max:255',
            'project_url' => 'sometimes|nullable|string|max:500',
            'is_featured' => 'sometimes|nullable|boolean',
            'is_active' => 'sometimes|nullable|boolean',
            'sort_order' => 'sometimes|nullable|integer',
        ];
    }
}
