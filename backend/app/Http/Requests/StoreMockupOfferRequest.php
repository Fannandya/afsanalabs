<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMockupOfferRequest extends FormRequest
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
            'description' => 'required|string',
            'feature_bullets' => 'nullable|array',
            'price' => 'required|numeric',
            'cta_label' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:500',
        ];
    }
}
