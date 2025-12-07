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
        Schema::table('product_lots', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('quantity')->comment('Lote activo/inactivo para control de inventario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_lots', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
