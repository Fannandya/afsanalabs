<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('footer_tagline')->nullable();
            $table->string('footer_copyright')->nullable();
            $table->string('contact_email');
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->text('whatsapp_message_template')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->string('footer_map_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
