<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreAboutTimelineRequest;
use App\Http\Requests\UpdateAboutTimelineRequest;
use App\Models\AboutTimelineItem;

class AboutTimelineController extends CrudController
{
    protected function modelClass(): string
    {
        return AboutTimelineItem::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreAboutTimelineRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateAboutTimelineRequest::class;
    }

    protected function imageFields(): array
    {
        return ['image_url'];
    }

    protected function orderable(): bool
    {
        return true;
    }
}
