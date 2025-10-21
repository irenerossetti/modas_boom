<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipo_producto', function (Blueprint $table) {
            $table->id('id_tipoProducto');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('dificultad_produccion', 50)->nullable();
            $table->boolean('habilitado')->default(true);
            $table->timestamps();
        });
        
        DB::statement("ALTER TABLE tipo_producto ADD COLUMN tiempo_produccion interval NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_producto');
    }
};
