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
        Schema::create('sale_item_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('cascade');
            $table->foreignId('product_lot_id')->constrained('product_lots')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->string('lot_number', 50);
            $table->date('expiration_date')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('sale_item_id');
            $table->index('product_lot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_lots');
    }
};
