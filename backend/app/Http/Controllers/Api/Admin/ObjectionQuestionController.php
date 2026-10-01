<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreObjectionQuestionRequest;
use App\Http\Requests\UpdateObjectionQuestionRequest;
use App\Models\ObjectionQuestion;

class ObjectionQuestionController extends CrudController
{
    protected function modelClass(): string
    {
        return ObjectionQuestion::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreObjectionQuestionRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateObjectionQuestionRequest::class;
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
