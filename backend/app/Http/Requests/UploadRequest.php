<?php

namespace App\Http\Requests;

use App\Rules\ImageMagicBytes;
use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,webp', new ImageMagicBytes],
            'folder' => ['nullable', 'regex:/^[a-z-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'Ukuran file terlalu besar (maks 2MB).',
        ];
    }
}
