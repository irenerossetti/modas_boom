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
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario'); // Primary key con nombre personalizado
            $table->foreignId('id_rol')->nullable()->constrained('rol', 'id_rol')->nullOnDelete();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('habilitado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
