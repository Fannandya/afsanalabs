<?php

namespace App\Http\Controllers\Api\Admin\Concerns;

use App\Http\Controllers\Controller;
use App\Models\PricePackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

abstract class CrudController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function storeRequestClass(): string;

    abstract protected function updateRequestClass(): string;

    /** @return array<string> */
    protected function imageFields(): array
    {
        return [];
    }

    protected function orderable(): bool
    {
        return true;
    }

    /** @return array<string> */
    protected function with(): array
    {
        return [];
    }

    public function index()
    {
        $model = $this->modelClass();
        $query = $model::query();
        if ($this->with() !== []) {
            $query->with($this->with());
        }
        if ($this->orderable()) {
            $query->orderBy('sort_order')->orderBy('id');
        } else {
            $query->orderBy('id');
        }

        return response()->json(['data' => $query->get()]);
    }

    public function show(string $id)
    {
        $model = $this->modelClass();

        return response()->json(['data' => $model::findOrFail($id)]);
    }

    public function store(Request $request)
    {
        /** @var FormRequest $form */
        $form = app($this->storeRequestClass());
        $validated = $request->validate($form->rules(), method_exists($form, 'messages') ? $form->messages() : []);
        $model = $this->modelClass();

        $row = DB::transaction(function () use ($model, $validated) {
            $this->handleSingleRecommended($model, $validated);

            return $model::create($validated);
        });

        return response()->json(['data' => $row->fresh()], 201);
    }

    public function update(Request $request, string $id)
    {
        /** @var FormRequest $form */
        $form = app($this->updateRequestClass());
        $form->setRouteResolver(fn () => $request->route());
        $validated = $request->validate($form->rules(), method_exists($form, 'messages') ? $form->messages() : []);
        $model = $this->modelClass();
        $row = $model::findOrFail($id);
        $oldFiles = $this->collectOldFiles($row);

        DB::transaction(function () use ($row, $model, $validated) {
            $this->handleSingleRecommended($model, $validated, $row->getKey());
            $row->update($validated);
        });

        $this->deleteReplacedFiles($oldFiles, $row->fresh());

        return response()->json(['data' => $row->fresh()]);
    }

    public function destroy(string $id)
    {
        $model = $this->modelClass();
        $row = $model::findOrFail($id);
        $oldFiles = $this->collectOldFiles($row);
        $row->delete();
        $this->deleteFiles($oldFiles);

        return response()->noContent();
    }

    /** @param array<string,mixed> $validated */
    protected function handleSingleRecommended(string $model, array $validated, mixed $exceptId = null): void
    {
        if ($model === PricePackage::class && ($validated['is_recommended'] ?? false)) {
            $q = $model::where('is_recommended', true);
            if ($exceptId !== null) {
                $q->where('id', '!=', $exceptId);
            }
            $q->update(['is_recommended' => false]);
        }
    }

    /** @return array<string> */
    protected function collectOldFiles(mixed $row): array
    {
        $out = [];
        foreach ($this->imageFields() as $field) {
            $v = $row->{$field} ?? null;
            if (is_string($v) && $v !== '') {
                $rel = $this->toRelative($v);
                if ($rel) {
                    $out[] = $rel;
                }
            }
        }

        return $out;
    }

    protected function deleteReplacedFiles(array $oldFiles, mixed $fresh): void
    {
        $freshRels = [];
        foreach ($this->imageFields() as $field) {
            $v = $fresh->{$field} ?? null;
            if (is_string($v) && $v !== '') {
                $rel = $this->toRelative($v);
                if ($rel) {
                    $freshRels[$rel] = true;
                }
            }
        }
        $this->deleteFiles(array_values(array_filter($oldFiles, fn ($f) => ! isset($freshRels[$f]))));
    }

    /** @param array<string> $files */
    protected function deleteFiles(array $files): void
    {
        foreach ($files as $rel) {
            if (str_contains($rel, '..')) {
                continue;
            }
            try {
                if (Storage::disk('public')->exists($rel)) {
                    Storage::disk('public')->delete($rel);
                }
            } catch (\Throwable) {
            }
        }
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
