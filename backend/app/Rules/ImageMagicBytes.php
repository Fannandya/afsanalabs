<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageMagicBytes implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $path = is_object($value) && method_exists($value, 'getRealPath') ? $value->getRealPath() : null;
        if (! $path || ! is_file($path)) {
            $fail('Berkas gambar tidak valid.');

            return;
        }
        $bytes = @file_get_contents($path, false, null, 0, 12);
        if ($bytes === false) {
            $fail('Berkas gambar tidak valid.');

            return;
        }
        $isJpeg = strlen($bytes) >= 3 && $bytes[0] === "\xFF" && $bytes[1] === "\xD8" && $bytes[2] === "\xFF";
        $isPng = strlen($bytes) >= 8 && substr($bytes, 0, 8) === "\x89PNG\r\n\x1A\n";
        $isWebp = strlen($bytes) >= 12 && substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP';
        if (! ($isJpeg || $isPng || $isWebp)) {
            $fail('Berkas harus gambar JPG/PNG/WEBP yang valid.');
        }
    }
}
