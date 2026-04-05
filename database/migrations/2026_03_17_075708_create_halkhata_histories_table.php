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
        Schema::create('halkhata_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('fiscal_year', 20); // e.g., "1430-1431"
            $table->decimal('opening_due', 15, 2)->default(0.00);
            $table->decimal('closing_due', 15, 2)->default(0.00);
            $table->decimal('total_paid_in_year', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['customer_id', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halkhata_histories');
    }
};
