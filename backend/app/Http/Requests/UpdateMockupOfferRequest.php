<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMockupOfferRequest extends FormRequest
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
            'description' => 'sometimes|string',
            'feature_bullets' => 'sometimes|nullable|array',
            'price' => 'sometimes|numeric',
            'cta_label' => 'sometimes|string|max:255',
            'image_url' => 'sometimes|nullable|string|max:500',
        ];
    }
}
