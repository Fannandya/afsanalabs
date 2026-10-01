<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\PricePackage;

class PackageController extends CrudController
{
    protected function modelClass(): string
    {
        return PricePackage::class;
    }

    protected function storeRequestClass(): string
    {
        return StorePackageRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdatePackageRequest::class;
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
