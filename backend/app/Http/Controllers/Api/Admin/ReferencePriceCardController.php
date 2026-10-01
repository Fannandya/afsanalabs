<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreReferencePriceCardRequest;
use App\Http\Requests\UpdateReferencePriceCardRequest;
use App\Models\ReferencePriceCard;

class ReferencePriceCardController extends CrudController
{
    protected function modelClass(): string
    {
        return ReferencePriceCard::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreReferencePriceCardRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateReferencePriceCardRequest::class;
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
