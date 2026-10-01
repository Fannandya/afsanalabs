<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroContent extends Model
{
    protected $table = 'hero_content';

    protected $fillable = ['eyebrow', 'heading', 'subheading', 'cta_label', 'cta_target', 'trust_badge_text', 'image_url', 'text_color'];
}
