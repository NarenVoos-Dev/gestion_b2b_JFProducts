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
            
            // Información del lote (opcional - admin puede asignar después)
            $table->foreignId('product_lot_id')->nullable()->constrained('product_lots')->onDelete('set null')->comment('Lote seleccionado por el cliente');
            $table->string('lot_number')->nullable()->comment('Número de lote para referencia rápida');
            $table->date('lot_expiration_date')->nullable()->comment('Fecha de vencimiento del lote');
            
            // Sistema de expiración de carrito
            $table->timestamp('expiration_date')->nullable()->comment('Fecha de expiración del item en carrito (24h)');
            $table->integer('extension_count')->default(0)->comment('Número de prórrogas solicitadas (máx 3)');
            $table->timestamp('extension_requested_at')->nullable()->comment('Fecha de última solicitud de prórroga');
            $table->enum('extension_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->comment('Estado de la prórroga');
            
            $table->timestamps();
            
            // Índice único: mismo producto + mismo lote = actualizar cantidad
            // Si no hay lote, permite múltiples items del mismo producto
            $table->unique(['user_id', 'product_id', 'product_lot_id'], 'cart_user_product_lot_unique');
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
