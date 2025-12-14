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
        Schema::create('metodo_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Efectivo, Tarjeta, QR, etc.
            $table->string('tipo', 50); // manual, automatico, qr
            $table->text('descripcion')->nullable();
            $table->string('icono', 100)->nullable(); // Clase CSS del icono
            $table->string('color', 20)->default('#6B7280'); // Color del método
            $table->json('configuracion')->nullable(); // Configuraciones específicas
            $table->string('qr_image')->nullable(); // Ruta de imagen QR personalizada
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0); // Orden de visualización
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodo_pagos');
    }
};
