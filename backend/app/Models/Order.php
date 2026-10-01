<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = ['tracking_code', 'order_type', 'package_id', 'mockup_fee', 'related_mockup_order_id', 'name', 'email', 'phone', 'notes', 'status'];

    protected function casts(): array
    {
        return [
            'mockup_fee' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PricePackage::class, 'package_id');
    }
}
