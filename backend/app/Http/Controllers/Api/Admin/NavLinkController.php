<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreNavLinkRequest;
use App\Http\Requests\UpdateNavLinkRequest;
use App\Models\NavLink;

class NavLinkController extends CrudController
{
    protected function modelClass(): string
    {
        return NavLink::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreNavLinkRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateNavLinkRequest::class;
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
