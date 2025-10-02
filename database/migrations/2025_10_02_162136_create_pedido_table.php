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
        Schema::create('pedido', function (Blueprint $table) {
            $table->id('id_pedido'); // Clave primaria personalizada
            $table->unsignedBigInteger('id_cliente'); // Foreign key hacia clientes
            $table->string('estado')->default('En proceso'); // Estado del pedido
            $table->decimal('total', 10, 2)->nullable(); // Total del pedido (opcional por ahora)
            $table->timestamps();

            // Foreign key constraint - referencia a la columna 'id' de clientes
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido');
    }
};
