<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreValuePropRequest;
use App\Http\Requests\UpdateValuePropRequest;
use App\Models\ValueProp;

class ValuePropController extends CrudController
{
    protected function modelClass(): string
    {
        return ValueProp::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreValuePropRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateValuePropRequest::class;
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
