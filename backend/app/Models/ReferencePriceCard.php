<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class ReferencePriceCard extends Model
{
    use HasContentScopes;

    protected $table = 'reference_price_cards';

    protected $fillable = ['label', 'price_value', 'price_note', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
