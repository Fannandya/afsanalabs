<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreProcessStepRequest;
use App\Http\Requests\UpdateProcessStepRequest;
use App\Models\ProcessStep;

class ProcessStepController extends CrudController
{
    protected function modelClass(): string
    {
        return ProcessStep::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreProcessStepRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateProcessStepRequest::class;
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
