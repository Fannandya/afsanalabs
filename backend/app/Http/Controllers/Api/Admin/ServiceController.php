<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;

class ServiceController extends CrudController
{
    protected function modelClass(): string
    {
        return Service::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreServiceRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateServiceRequest::class;
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
