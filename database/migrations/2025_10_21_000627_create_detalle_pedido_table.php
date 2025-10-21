<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id('id_detallePedido');

            $table->unsignedBigInteger('id_pedido');
            $table->foreign('id_pedido')
                  ->references('id_pedido')->on('pedido')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('id_producto');
            $table->foreign('id_producto')
                  ->references('id_producto')->on('producto')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->decimal('cantidad', 12, 2)->nullable();       
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();

            $table->timestamps();

            $table->index('id_pedido', 'detalle_pedido_idx_pedido');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
