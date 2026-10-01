<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessSettingRequest extends FormRequest
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
            'business_name' => 'sometimes|string|max:255',
            'footer_tagline' => 'sometimes|nullable|string|max:255',
            'footer_copyright' => 'sometimes|nullable|string|max:255',
            'contact_email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'whatsapp_number' => 'sometimes|nullable|string|max:50',
            'whatsapp_message_template' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'payment_instructions' => 'sometimes|nullable|string',
            'footer_map_url' => 'sometimes|nullable|string|max:500',
        ];
    }
}
