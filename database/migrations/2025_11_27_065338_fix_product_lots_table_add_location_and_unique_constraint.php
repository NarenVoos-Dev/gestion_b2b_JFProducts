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
        // 1. Verificar y eliminar el índice único antiguo si existe
        $indexExists = DB::select("SHOW INDEX FROM product_lots WHERE Key_name = 'product_lots_lot_number_unique'");
        
        if (!empty($indexExists)) {
            Schema::table('product_lots', function (Blueprint $table) {
                $table->dropUnique('product_lots_lot_number_unique');
            });
        }
        
        // 2. Verificar si location_id ya existe
        if (!Schema::hasColumn('product_lots', 'location_id')) {
            Schema::table('product_lots', function (Blueprint $table) {
                // Agregar la columna location_id como NULLABLE primero
                $table->foreignId('location_id')
                    ->after('product_id')
                    ->nullable()
                    ->constrained('locations')
                    ->onDelete('cascade');
            });
            
            // 3. Asignar una bodega por defecto a los lotes existentes
            $defaultLocation = DB::table('locations')->first();
            if ($defaultLocation) {
                DB::table('product_lots')
                    ->whereNull('location_id')
                    ->update(['location_id' => $defaultLocation->id]);
            }
            
            // 4. Ahora hacer la columna NOT NULL
            DB::statement('ALTER TABLE product_lots MODIFY location_id BIGINT UNSIGNED NOT NULL');
        }
        
        // 5. Verificar y crear índice único compuesto si no existe
        $compositeIndexExists = DB::select("SHOW INDEX FROM product_lots WHERE Key_name = 'product_lots_unique_constraint'");
        
        if (empty($compositeIndexExists)) {
            Schema::table('product_lots', function (Blueprint $table) {
                $table->unique(['product_id', 'location_id', 'lot_number'], 'product_lots_unique_constraint');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_lots', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropUnique('product_lots_unique_constraint');
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
            $table->unique('lot_number', 'product_lots_lot_number_unique');
        });
    }
};
