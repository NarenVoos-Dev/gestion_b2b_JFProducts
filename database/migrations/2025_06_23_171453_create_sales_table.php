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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->datetime('date'); // Cambiado de date a datetime para guardar hora
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('notes')->nullable();
            $table->boolean('is_cash')->default(true); // Usamos un booleano para 'Contado'. 'true' es Contado, 'false' es Crédito.
            $table->string('status')->default('Pagada')->comment('Valores: Pendiente, Facturado, Entregado, Finalizado');
            
            // Campos para facturación
            $table->string('invoice_number')->nullable()->comment('Número de factura');
            $table->string('invoice_pdf_path')->nullable()->comment('Ruta del PDF de factura subido');
            $table->timestamp('invoiced_at')->nullable()->comment('Fecha y hora de facturación');
            
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2)->comment('Precio de venta por unidad');
            $table->decimal('tax_rate', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
