<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    use HasContentScopes;

    protected $table = 'portfolios';

    protected $fillable = ['category_id', 'title', 'image_url', 'description', 'client_name', 'project_url', 'is_featured', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
