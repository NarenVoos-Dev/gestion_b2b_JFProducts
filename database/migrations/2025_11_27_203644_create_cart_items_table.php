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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->comment('Precio al momento de agregar al carrito');
            
            // Información adicional para renderizado rápido del carrito
            $table->string('image_url')->nullable()->comment('URL completa de la imagen del producto');
            $table->string('product_name')->comment('Nombre del producto');
            $table->string('laboratory')->nullable()->comment('Nombre del laboratorio');
            
            $table->timestamps();
            
            // Índice único para evitar duplicados del mismo producto por usuario
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
