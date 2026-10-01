<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroRequest extends FormRequest
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
            'eyebrow' => 'sometimes|nullable|string|max:255',
            'heading' => 'sometimes|string|max:255',
            'subheading' => 'sometimes|nullable|string',
            'cta_label' => 'sometimes|nullable|string|max:255',
            'cta_target' => 'sometimes|nullable|string|max:255',
            'trust_badge_text' => 'sometimes|nullable|string|max:255',
            'image_url' => 'sometimes|nullable|string|max:500',
            'text_color' => 'sometimes|nullable|regex:/^#[0-9a-fA-F]{6}$/',
        ];
    }
}
