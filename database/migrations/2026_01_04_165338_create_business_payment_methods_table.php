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
        Schema::create('business_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            
            // Tipo de método de pago
            $table->enum('type', ['bank_account', 'qr_code', 'payment_link', 'cash', 'other'])
                ->comment('Tipo de método de pago');
            
            // Información bancaria
            $table->string('bank_name')->nullable()->comment('Nombre del banco');
            $table->enum('account_type', ['ahorros', 'corriente'])->nullable()->comment('Tipo de cuenta');
            $table->string('account_number')->nullable()->comment('Número de cuenta');
            $table->string('account_holder')->nullable()->comment('Titular de la cuenta');
            
            // QR Code
            $table->string('qr_code_image')->nullable()->comment('Ruta a imagen QR');
            
            // Link de pago
            $table->string('payment_link')->nullable()->comment('URL de pago (PSE, Nequi, etc.)');
            
            // Descripción adicional
            $table->text('description')->nullable()->comment('Descripción o instrucciones adicionales');
            
            // Control
            $table->boolean('is_active')->default(true)->comment('Método activo');
            $table->integer('display_order')->default(0)->comment('Orden de visualización');
            
            $table->timestamps();
            
            // Índices
            $table->index(['business_id', 'is_active']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_payment_methods');
    }
};
