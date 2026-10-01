<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProcessStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step_number' => 'sometimes|integer|min:1',
            'icon' => 'sometimes|nullable|string|max:255',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'sort_order' => 'sometimes|nullable|integer',
            'is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
