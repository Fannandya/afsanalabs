<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Models\Portfolio;

class PortfolioController extends CrudController
{
    protected function modelClass(): string
    {
        return Portfolio::class;
    }

    protected function storeRequestClass(): string
    {
        return StorePortfolioRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdatePortfolioRequest::class;
    }

    protected function imageFields(): array
    {
        return ['image_url'];
    }

    protected function orderable(): bool
    {
        return true;
    }

    protected function with(): array
    {
        return ['category'];
    }
}
