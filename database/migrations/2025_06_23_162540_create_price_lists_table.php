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
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('Ej: Lista Institucional, Descuento VIP');

            // El campo que pediste para saber si es precio o descuento
            $table->enum('type', ['markup', 'discount'])->comment('markup=Aumento, discount=Descuento');

            $table->decimal('percentage', 5, 2)->comment('El porcentaje a aplicar. Ej: 5.00 para 5%');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
