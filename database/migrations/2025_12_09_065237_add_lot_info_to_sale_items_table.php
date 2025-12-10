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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_lot_id')->nullable()->constrained('product_lots')->onDelete('set null')->after('product_id');
            $table->string('lot_number')->nullable()->after('product_lot_id');
            $table->date('expiration_date')->nullable()->after('lot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_lot_id']);
            $table->dropColumn(['product_lot_id', 'lot_number', 'expiration_date']);
        });
    }
};
