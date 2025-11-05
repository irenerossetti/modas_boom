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
        Schema::create('avance_produccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pedido');
            $table->string('etapa'); // Corte, Confección, Acabado, Control de Calidad
            $table->integer('porcentaje_avance');
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('registrado_por');
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avance_produccion');
    }
};