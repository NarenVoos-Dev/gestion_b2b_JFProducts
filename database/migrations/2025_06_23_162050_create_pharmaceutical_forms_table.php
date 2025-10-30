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
        Schema::create('pharmaceutical_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Tableta", "Jarabe", "Inyección"
            $table->string('abbreviation')->nullable(); // Ej: "TAB", "JBE"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_forms');
    }
};
