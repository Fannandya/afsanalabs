<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\SingletonController;
use App\Http\Requests\UpdateHeroRequest;
use App\Models\HeroContent;

class HeroController extends SingletonController
{
    protected function modelClass(): string
    {
        return HeroContent::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateHeroRequest::class;
    }

    protected function imageFields(): array
    {
        return ['image_url'];
    }
}
