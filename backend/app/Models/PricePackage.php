<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricePackage extends Model
{
    use HasContentScopes;

    protected $table = 'price_packages';

    protected $fillable = ['name', 'tagline', 'price', 'show_price', 'features', 'is_recommended', 'cta_label', 'cta_action', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_price' => 'boolean',
            'is_recommended' => 'boolean',
            'features' => 'array',
            'price' => 'decimal:2',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
