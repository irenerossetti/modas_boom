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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->foreignId('id_usuario')->nullable()->constrained('usuario', 'id_usuario')->nullOnDelete();
            $table->string('accion', 50);
            $table->string('modulo', 50);
            $table->text('descripcion');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('id_usuario');
            $table->index('accion');
            $table->index('modulo');
            $table->index('created_at');
            $table->index(['accion', 'modulo']);
            $table->index(['created_at', 'id_usuario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
