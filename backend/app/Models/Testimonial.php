<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasContentScopes;

    protected $table = 'testimonials';

    protected $fillable = ['client_name', 'content', 'rating', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
