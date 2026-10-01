<?php

namespace App\Models;

use App\Models\Concerns\HasContentScopes;
use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    use HasContentScopes;

    protected $table = 'process_steps';

    protected $fillable = ['step_number', 'icon', 'title', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
