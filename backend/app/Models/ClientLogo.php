<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class ClientLogo extends Model
{
    use HasContentScopes;

    protected $table = 'client_logos';

    protected $fillable = ['name', 'logo_url', 'link_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
