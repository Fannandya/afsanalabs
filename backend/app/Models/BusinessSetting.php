<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $table = 'business_settings';

    protected $fillable = ['business_name', 'footer_tagline', 'footer_copyright', 'contact_email', 'phone', 'whatsapp_number', 'whatsapp_message_template', 'address', 'description', 'payment_instructions', 'footer_map_url'];
}
