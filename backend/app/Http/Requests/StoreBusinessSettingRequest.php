<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessSettingRequest extends FormRequest
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
            'business_name' => 'required|string|max:255',
            'footer_tagline' => 'nullable|string|max:255',
            'footer_copyright' => 'nullable|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_message_template' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'payment_instructions' => 'nullable|string',
            'footer_map_url' => 'nullable|string|max:500',
        ];
    }
}
