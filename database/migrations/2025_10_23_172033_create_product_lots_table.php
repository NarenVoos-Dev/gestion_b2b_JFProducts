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
        Schema::create('product_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');

            $table->string('lot_number')->comment('Número de lote');
            $table->date('expiration_date')->comment('Fecha de vencimiento');
            $table->decimal('quantity', 10, 2)->comment('Cantidad actual de este lote');
            
            $table->decimal('cost', 10, 2)->nullable()->comment('Costo de adquisición de este lote');
            $table->decimal('stock_minimo', 10, 2)->default(0)->comment('Stock mínimo para este lote/producto');

            $table->timestamps();

            $table->unique(['product_id', 'location_id', 'lot_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_lots');
    }
};
