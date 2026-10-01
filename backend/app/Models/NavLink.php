<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class NavLink extends Model
{
    use HasContentScopes;

    protected $table = 'nav_links';

    protected $fillable = ['label', 'target', 'placement', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
