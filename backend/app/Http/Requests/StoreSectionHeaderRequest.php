<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eyebrow_text' => 'nullable|string|max:255',
            'heading' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro_text' => 'nullable|string',
            'note_text' => 'nullable|string',
        ];
    }
}
