<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasContentScopes;

    protected $table = 'team_members';

    protected $fillable = ['name', 'role', 'photo_url', 'twitter_url', 'facebook_url', 'linkedin_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
