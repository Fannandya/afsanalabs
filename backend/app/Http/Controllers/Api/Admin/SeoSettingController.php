<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\SingletonController;
use App\Http\Requests\UpdateSeoSettingRequest;
use App\Models\SeoSetting;

class SeoSettingController extends SingletonController
{
    protected function modelClass(): string
    {
        return SeoSetting::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateSeoSettingRequest::class;
    }

    protected function imageFields(): array
    {
        return ['og_image_url'];
    }
}
