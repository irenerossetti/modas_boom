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
        Schema::create('observaciones_calidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pedido');
            $table->enum('tipo_observacion', ['Defecto', 'Mejora', 'Aprobado', 'Rechazado']);
            $table->string('area_afectada'); // Costura, Tela, Acabado, etc.
            $table->text('descripcion');
            $table->enum('prioridad', ['Baja', 'Media', 'Alta', 'Crítica']);
            $table->enum('estado', ['Pendiente', 'En corrección', 'Corregido', 'Cerrado'])->default('Pendiente');
            $table->text('accion_correctiva')->nullable();
            $table->integer('registrado_por');
            $table->integer('corregido_por')->nullable();
            $table->timestamp('fecha_correccion')->nullable();
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('corregido_por')->references('id_usuario')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observaciones_calidad');
    }
};