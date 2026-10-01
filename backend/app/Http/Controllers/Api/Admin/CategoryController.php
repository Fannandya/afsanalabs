<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends CrudController
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreCategoryRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateCategoryRequest::class;
    }

    protected function imageFields(): array
    {
        return [];
    }

    protected function orderable(): bool
    {
        return true;
    }
}
