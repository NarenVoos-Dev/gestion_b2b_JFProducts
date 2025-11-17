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
            $table->decimal('price_regulated_reg', 10, 2)->nullable()->comment('Precio regulado regional');
            
            $table->boolean('has_tax')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0); //IVA
            
            $table->decimal('stock_minimo', 10, 2)->default(0)->comment('Stock mínimo para este lote/producto');

            $table->foreignId('molecule_id')->nullable()->constrained('molecules'); // MOLECULA
            $table->string('concentration')->nullable(); // CONCENTRACION
            $table->foreignId('commercial_name_id')->nullable()->constrained('commercial_names'); // NOMBRE_COMERCIAL
            $table->foreignId('laboratory_id')->nullable()->constrained('laboratories'); // LABORATORIO
            $table->boolean('cold_chain')->default(false); // CADENA_FRIO
            $table->boolean('controlled')->default(false); // CONTROLADO
            $table->boolean('regulated')->default(false); // regulado

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
