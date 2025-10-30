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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('product_channel_id')->nullable()->constrained();
            $table->foreignId('pharmaceutical_form_id')->nullable()->constrained();
            $table->foreignId('product_type_id')->nullable()->constrained();


            $table->string('name');
            $table->string('sku')->nullable()->unique()->comment('Stock Keeping Unit'); // Codigo unico del producto
            $table->string('unit')->default('unidad')->comment('Ej: unidad, metro, litro, caja');
            $table->decimal('price', 10, 2)->comment('Precio de venta al público');
            
            $table->string('molecule')->nullable(); // MOLECULA
            $table->string('concentration')->nullable(); // CONCENTRACION
            $table->string('commercial_presentation')->nullable(); // PRESENTACION_COMERCIAL
            $table->string('commercial_name')->nullable(); // NOMBRE_COMERCIAL
            $table->string('laboratory')->nullable(); // LABORATORIO
            $table->boolean('cold_chain')->default(false); // CADENA_FRIO
            $table->boolean('controlled')->default(false); // CONTROLADO
            $table->string('barcode')->nullable(); // COD_BARRAS
            $table->string('cum')->nullable(); // CUM
            $table->string('invima_registration')->nullable(); // INVIMA
            $table->string('atc_code')->nullable(); // ATC

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
