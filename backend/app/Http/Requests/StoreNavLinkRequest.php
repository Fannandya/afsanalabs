<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNavLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'target' => ['required', 'string', 'max:255', 'regex:/^(\/|#)/'],
            'placement' => ['required', 'in:topnav,footer'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'target.regex' => 'Target harus path SPA (/xxx) atau anchor (#xxx).',
        ];
    }
}
