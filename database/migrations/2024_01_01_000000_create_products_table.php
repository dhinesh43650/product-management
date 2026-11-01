<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('product_name');
            $table->unsignedInteger('quantity_in_stock')->default(0);
            $table->decimal('price_per_item', 10, 2)->default(0.00);
            $table->decimal('total_value', 14, 2)->storedAs('quantity_in_stock * price_per_item');
            $table->timestamp('datetime_submitted')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
