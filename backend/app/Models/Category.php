<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }
}
