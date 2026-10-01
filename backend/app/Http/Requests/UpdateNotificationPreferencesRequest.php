<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notify_new_order' => 'sometimes|boolean',
            'notify_contact_message' => 'sometimes|boolean',
            'notify_consultation' => 'sometimes|boolean',
            'notify_mockup_request' => 'sometimes|boolean',
        ];
    }
}
