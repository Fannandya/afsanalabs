<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = ['user_id', 'notify_new_order', 'notify_contact_message', 'notify_consultation', 'notify_mockup_request'];

    protected function casts(): array
    {
        return [
            'notify_new_order' => 'boolean',
            'notify_contact_message' => 'boolean',
            'notify_consultation' => 'boolean',
            'notify_mockup_request' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
