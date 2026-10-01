<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(UploadRequest $request)
    {
        $folder = $request->input('folder', 'misc');
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $ext = self::safeExtension($file);
        $name = (string) Str::uuid().'.'.$ext;
        $path = Storage::disk('public')->putFileAs($folder, $file, $name);

        return response()->json(['url' => '/storage/'.$path], 201);
    }

    private static function safeExtension(UploadedFile $file): string
    {
        $mime = (string) $file->getMimeType();
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        return $map[$mime] ?? 'bin';
    }
}
