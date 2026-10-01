<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_content', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('heading');
            $table->text('subheading')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_target')->nullable();
            $table->string('trust_badge_text')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('text_color', 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_content');
    }
};
