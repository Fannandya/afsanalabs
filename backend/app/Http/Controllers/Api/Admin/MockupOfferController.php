<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\SingletonController;
use App\Http\Requests\UpdateMockupOfferRequest;
use App\Models\MockupOffer;

class MockupOfferController extends SingletonController
{
    protected function modelClass(): string
    {
        return MockupOffer::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateMockupOfferRequest::class;
    }

    protected function imageFields(): array
    {
        return ['image_url'];
    }
}
