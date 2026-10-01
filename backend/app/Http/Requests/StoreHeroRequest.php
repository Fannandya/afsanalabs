<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['text_color'] as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'eyebrow' => 'nullable|string|max:255',
            'heading' => 'required|string|max:255',
            'subheading' => 'nullable|string',
            'cta_label' => 'nullable|string|max:255',
            'cta_target' => 'nullable|string|max:255',
            'trust_badge_text' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:500',
            'text_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
        ];
    }
}
