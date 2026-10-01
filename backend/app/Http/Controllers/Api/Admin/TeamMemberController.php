<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\CrudController;
use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\TeamMember;

class TeamMemberController extends CrudController
{
    protected function modelClass(): string
    {
        return TeamMember::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreTeamMemberRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateTeamMemberRequest::class;
    }

    protected function imageFields(): array
    {
        return ['photo_url'];
    }

    protected function orderable(): bool
    {
        return true;
    }
}
