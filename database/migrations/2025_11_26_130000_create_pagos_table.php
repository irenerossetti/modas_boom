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
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->decimal('monto', 14, 2);
            $table->string('metodo')->nullable();
            $table->string('referencia')->nullable();
            $table->timestamp('fecha_pago')->useCurrent();
            $table->integer('registrado_por');
            $table->boolean('anulado')->default(false);
            $table->integer('anulado_por')->nullable();
            $table->text('anulado_motivo')->nullable();
            $table->string('recibo_path')->nullable();
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('set null');
            $table->foreign('registrado_por')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->index(['id_pedido', 'id_cliente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};
