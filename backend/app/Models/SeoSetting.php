<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $table = 'seo_settings';

    protected $fillable = ['meta_title', 'meta_description', 'meta_keywords', 'og_image_url'];
}
