<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|string|max:255',
            'photo_url' => 'sometimes|string|max:500',
            'twitter_url' => 'sometimes|nullable|string|max:500',
            'facebook_url' => 'sometimes|nullable|string|max:500',
            'linkedin_url' => 'sometimes|nullable|string|max:500',
            'sort_order' => 'sometimes|nullable|integer',
            'is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
