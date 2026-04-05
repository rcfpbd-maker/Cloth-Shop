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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('village', 255)->nullable();
            $table->string('nid', 50)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->string('risk_level', 50)->nullable();
            $table->decimal('previous_due', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
