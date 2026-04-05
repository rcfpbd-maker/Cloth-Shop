<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0.00);
            $table->decimal('sale_price', 15, 2)->default(0.00);
            $table->decimal('wholesale_price', 15, 2)->default(0.00);
            $table->decimal('minimum_sale_price', 15, 2)->default(0.00);
            $table->string('sku', 100)->unique()->nullable();
            $table->string('barcode', 100)->nullable();
            $table->timestamps();

            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
