<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Models\Testimonial;

class TestimonialController extends CrudController
{
    protected function modelClass(): string
    {
        return Testimonial::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreTestimonialRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateTestimonialRequest::class;
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
