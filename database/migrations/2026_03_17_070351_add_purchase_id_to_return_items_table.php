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
        Schema::table('return_items', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->after('id')->constrained('purchases')->onDelete('cascade');
            // We need to modify the existing sale_id to be nullable.
            // Note: Since this is SQLite/MySQL compatibility, we should just drop the constraint and re-add it if needed, or use doctrine/dbal.
            // For Laravel 10+, change() doesn't need doctrine/dbal for simple changes in MySQL.
            $table->unsignedBigInteger('sale_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropColumn('purchase_id');
            $table->unsignedBigInteger('sale_id')->nullable(false)->change();
        });
    }
};
