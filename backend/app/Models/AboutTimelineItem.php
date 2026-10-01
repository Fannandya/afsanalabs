<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class AboutTimelineItem extends Model
{
    use HasContentScopes;

    protected $table = 'about_timeline_items';

    protected $fillable = ['period', 'title', 'description', 'image_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
