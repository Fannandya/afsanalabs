<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreClientLogoRequest;
use App\Http\Requests\UpdateClientLogoRequest;
use App\Models\ClientLogo;

class ClientLogoController extends CrudController
{
    protected function modelClass(): string
    {
        return ClientLogo::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreClientLogoRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateClientLogoRequest::class;
    }

    protected function imageFields(): array
    {
        return ['logo_url'];
    }

    protected function orderable(): bool
    {
        return true;
    }
}
