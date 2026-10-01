<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Models\Faq;

class FaqController extends CrudController
{
    protected function modelClass(): string
    {
        return Faq::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreFaqRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateFaqRequest::class;
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
