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
        Schema::create('pedido_prenda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('prenda_id');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->string('talla')->nullable();
            $table->string('color')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices y claves foráneas
            $table->foreign('pedido_id')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('prenda_id')->references('id')->on('prendas')->onDelete('cascade');
            
            // Índices para mejor performance
            $table->index(['pedido_id', 'prenda_id']);
            $table->index('pedido_id');
            $table->index('prenda_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_prenda');
    }
};
