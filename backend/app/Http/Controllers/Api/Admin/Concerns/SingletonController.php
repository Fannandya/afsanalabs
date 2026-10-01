<?php

namespace App\Http\Controllers\Api\Admin\Concerns;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

abstract class SingletonController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function updateRequestClass(): string;

    /** @return array<string> */
    protected function imageFields(): array
    {
        return [];
    }

    public function show()
    {
        $model = $this->modelClass();

        return response()->json(['data' => $model::find(1)]);
    }

    public function update(Request $request)
    {
        /** @var FormRequest $form */
        $form = app($this->updateRequestClass());
        $rules = $form->rules();
        $messages = method_exists($form, 'messages') ? $form->messages() : [];
        if ($request->has('text_color') && $request->input('text_color') === '') {
            $request->merge(['text_color' => null]);
        }
        $validated = $request->validate($rules, $messages);
        $model = $this->modelClass();
        $row = $model::firstOrNew(['id' => 1]);
        $oldFiles = [];
        foreach ($this->imageFields() as $field) {
            $v = $row->{$field} ?? null;
            if (is_string($v) && $v !== '') {
                $rel = $this->toRelative($v);
                if ($rel) {
                    $oldFiles[] = $rel;
                }
            }
        }
        $row->fill($validated);
        $row->id = 1;
        $row->save();
        $freshRels = [];
        foreach ($this->imageFields() as $field) {
            $v = $row->{$field} ?? null;
            if (is_string($v) && $v !== '') {
                $rel = $this->toRelative($v);
                if ($rel) {
                    $freshRels[$rel] = true;
                }
            }
        }
        foreach ($oldFiles as $rel) {
            if (! isset($freshRels[$rel]) && ! str_contains($rel, '..')) {
                try {
                    if (Storage::disk('public')->exists($rel)) {
                        Storage::disk('public')->delete($rel);
                    }
                } catch (\Throwable) {
                }
            }
        }

        return response()->json(['data' => $row->fresh()]);
    }

    protected function toRelative(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, '/storage/')) {
            return ltrim(substr($url, 9), '/');
        }
        if (str_contains($url, '/storage/')) {
            return ltrim(substr($url, strpos($url, '/storage/') + 9), '/');
        }

        return null;
    }
}
