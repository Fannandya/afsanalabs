<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code', 20)->unique();
            $table->enum('order_type', ['paket', 'mockup']);
            $table->foreignId('package_id')->nullable()->constrained('price_packages')->nullOnDelete();
            $table->decimal('mockup_fee', 12, 2)->nullable();
            $table->unsignedBigInteger('related_mockup_order_id')->nullable()->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('notes')->nullable();
            $table->enum('status', ['menunggu_konfirmasi', 'menunggu_pembayaran', 'dalam_pengerjaan', 'selesai', 'dibatalkan'])->default('menunggu_konfirmasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
