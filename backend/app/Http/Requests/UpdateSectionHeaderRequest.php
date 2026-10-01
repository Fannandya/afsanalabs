<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eyebrow_text' => 'sometimes|nullable|string|max:255',
            'heading' => 'sometimes|string|max:255',
            'subtitle' => 'sometimes|nullable|string|max:255',
            'intro_text' => 'sometimes|nullable|string',
            'note_text' => 'sometimes|nullable|string',
        ];
    }
}
