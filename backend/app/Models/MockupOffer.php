<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockupOffer extends Model
{
    protected $table = 'mockup_offer';

    protected $fillable = ['eyebrow', 'heading', 'description', 'feature_bullets', 'price', 'cta_label', 'image_url'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'feature_bullets' => 'array',
        ];
    }
}
