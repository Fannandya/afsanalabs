<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionHeader extends Model
{
    protected $table = 'section_headers';

    protected $fillable = ['section_key', 'eyebrow_text', 'heading', 'subtitle', 'intro_text', 'note_text'];
}
