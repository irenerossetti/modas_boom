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
        Schema::table('pedido', function (Blueprint $table) {
            $table->integer('calificacion')->nullable()->after('confirmacion_recepcion');
            $table->text('comentario_calificacion')->nullable()->after('calificacion');
            $table->timestamp('fecha_calificacion')->nullable()->after('comentario_calificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn(['calificacion', 'comentario_calificacion', 'fecha_calificacion']);
        });
    }
};
