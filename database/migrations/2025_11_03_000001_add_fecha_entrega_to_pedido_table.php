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
            $table->date('fecha_entrega_programada')->nullable()->after('total');
            $table->text('observaciones_entrega')->nullable()->after('fecha_entrega_programada');
            $table->integer('reprogramado_por')->nullable()->after('observaciones_entrega');
            $table->timestamp('fecha_reprogramacion')->nullable()->after('reprogramado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_entrega_programada',
                'observaciones_entrega',
                'reprogramado_por',
                'fecha_reprogramacion'
            ]);
        });
    }
};