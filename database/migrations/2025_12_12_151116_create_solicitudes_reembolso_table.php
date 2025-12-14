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
        Schema::create('solicitudes_reembolso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_id');
            $table->unsignedBigInteger('pedido_id');
            $table->string('tipo_reembolso'); // error_sistema, pedido_cancelado, solicitud_cliente
            $table->text('motivo_detallado');
            $table->string('beneficiario_nombre');
            $table->string('beneficiario_ci');
            $table->string('beneficiario_telefono');
            $table->string('beneficiario_email')->nullable();
            $table->string('metodo_reembolso'); // efectivo, transferencia
            $table->string('banco')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('estado')->default('pendiente'); // pendiente, procesado, rechazado
            $table->unsignedBigInteger('solicitado_por');
            $table->unsignedBigInteger('procesado_por')->nullable();
            $table->timestamp('fecha_procesado')->nullable();
            $table->text('notas_procesamiento')->nullable();
            $table->timestamps();

            $table->foreign('pago_id')->references('id')->on('pago')->onDelete('cascade');
            $table->foreign('pedido_id')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('solicitado_por')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('procesado_por')->references('id_usuario')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_reembolso');
    }
};