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
        Schema::create('account_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_receivable_id')->constrained('accounts_receivable')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->date('payment_date');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índice para optimizar consultas
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_payments');
    }
};
