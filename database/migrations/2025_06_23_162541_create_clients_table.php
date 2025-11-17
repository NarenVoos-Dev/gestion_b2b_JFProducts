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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type_document')->nullable()->comment('Tipo de documento (NIT, C.C.)');
            $table->string('document')->unique()->nullable()->comment('Número de identificación o NIT');
            $table->string('email')->unique()->nullable();
            
            // Información de Contacto
            $table->string('phone1')->nullable()->comment('Teléfono principal');
            $table->string('phone2')->nullable()->comment('Teléfono secundario');
            $table->string('address')->nullable();
            //Lista de precio
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            // Logística y Crédito (Mantenemos zone_id de tu migración previa)
            $table->decimal('credit_limit', 15, 2)->default(0);

            // Control de Acceso y Estado de la Solicitud
            $table->boolean('is_active')->default(0)->comment('Estado de activación (0=Pendiente, 1=Activo)');
            
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
