<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_headers', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique();
            $table->string('eyebrow_text')->nullable();
            $table->string('heading');
            $table->string('subtitle')->nullable();
            $table->text('intro_text')->nullable();
            $table->text('note_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_headers');
    }
};
