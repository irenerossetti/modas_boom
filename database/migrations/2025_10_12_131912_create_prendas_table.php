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
        Schema::create('prendas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('categoria');
            $table->string('imagen')->nullable();
            $table->json('colores')->nullable(); // Array de colores disponibles
            $table->json('tallas')->nullable();  // Array de tallas disponibles
            $table->boolean('activo')->default(true);
            $table->integer('stock')->default(0);
            $table->timestamps();
            
            // Índices para mejor rendimiento
            $table->index('categoria');
            $table->index('activo');
            $table->index('precio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prendas');
    }
};
