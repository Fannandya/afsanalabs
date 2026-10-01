<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => ['sometimes', 'alpha_dash', 'max:255', Rule::unique('categories', 'slug')->ignore($id)],
            'sort_order' => 'sometimes|nullable|integer',
        ];
    }
}
