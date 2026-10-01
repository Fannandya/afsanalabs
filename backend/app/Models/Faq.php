<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasContentScopes;

    protected $table = 'faqs';

    protected $fillable = ['question', 'answer', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
