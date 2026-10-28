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
            // Mã TS-0001 gán ngay sau khi insert, trong cùng transaction (BR-10)
            $table->string('code', 20)->nullable()->unique();
            // Không xóa khách đã có đơn, chỉ khóa tài khoản (FR-14)
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receiver_name', 100);
            $table->string('receiver_phone', 15);
            $table->string('shipping_address', 255);
            $table->string('note', 500)->nullable();
            $table->decimal('subtotal', 12, 0);
            $table->decimal('discount', 12, 0)->default(0);
            $table->decimal('total', 12, 0);
            $table->enum('payment_method', ['cod'])->default('cod');
            $table->enum('status', ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])
                ->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
