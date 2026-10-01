<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class FooterLegalLink extends Model
{
    use HasContentScopes;

    protected $table = 'footer_legal_links';

    protected $fillable = ['label', 'url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
