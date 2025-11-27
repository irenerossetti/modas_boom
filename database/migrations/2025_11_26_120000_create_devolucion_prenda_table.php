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
        Schema::create('devolucion_prenda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_prenda');
            $table->integer('cantidad')->default(1);
            $table->text('motivo')->nullable();
            $table->integer('registrado_por');
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('id_prenda')->references('id')->on('prendas')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id_usuario')->on('usuario')->onDelete('cascade');

            $table->index(['id_pedido', 'id_prenda']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolucion_prenda');
    }
};
