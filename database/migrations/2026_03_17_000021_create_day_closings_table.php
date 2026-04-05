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
        Schema::create('day_closing', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date')->unique();
            $table->decimal('opening_cash', 15, 2)->default(0.00);
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('total_expense', 15, 2)->default(0.00);
            $table->decimal('total_collection', 15, 2)->default(0.00);
            $table->decimal('closing_cash', 15, 2)->default(0.00);
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_closings');
    }
};
