<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // SP đã bán chỉ được ẩn, không xóa cứng
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            // Lưu tên và giá tại thời điểm mua
            $table->string('product_name', 200);
            $table->decimal('price', 12, 0);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
