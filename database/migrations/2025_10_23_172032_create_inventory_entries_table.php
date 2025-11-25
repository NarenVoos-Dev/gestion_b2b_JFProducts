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
        Schema::create('inventory_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Usuario que registra
            $table->foreignId('location_id')->constrained()->onDelete('cascade'); // Bodega de destino
            //$table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete(); // Proveedor

            $table->string('city')->nullable(); // Ciudad (quemada)
            $table->string('reference')->nullable(); // Factura, Guía de remisión, etc.
            $table->date('entry_date');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_entries');
    }
};
