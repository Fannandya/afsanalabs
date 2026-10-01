<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreFooterLegalLinkRequest;
use App\Http\Requests\UpdateFooterLegalLinkRequest;
use App\Models\FooterLegalLink;

class FooterLegalLinkController extends CrudController
{
    protected function modelClass(): string
    {
        return FooterLegalLink::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreFooterLegalLinkRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateFooterLegalLinkRequest::class;
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
