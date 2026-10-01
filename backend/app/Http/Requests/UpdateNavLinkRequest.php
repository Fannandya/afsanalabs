<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNavLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'target' => ['sometimes', 'string', 'max:255', 'regex:/^(\/|#)/'],
            'placement' => ['sometimes', 'in:topnav,footer'],
            'sort_order' => ['sometimes', 'nullable', 'integer'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'target.regex' => 'Target harus path SPA (/xxx) atau anchor (#xxx).',
        ];
    }
}
