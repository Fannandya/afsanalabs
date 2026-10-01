<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\SingletonController;
use App\Http\Requests\UpdateBusinessSettingRequest;
use App\Models\BusinessSetting;

class BusinessSettingController extends SingletonController
{
    protected function modelClass(): string
    {
        return BusinessSetting::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateBusinessSettingRequest::class;
    }

    protected function imageFields(): array
    {
        return ['footer_map_url'];
    }
}
