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
            $table->boolean('recepcion_confirmada')->default(false)->after('estado');
            $table->timestamp('fecha_confirmacion_recepcion')->nullable()->after('recepcion_confirmada');
            $table->integer('confirmado_por')->nullable()->after('fecha_confirmacion_recepcion');
            $table->text('observaciones_recepcion')->nullable()->after('confirmado_por');
            $table->boolean('notificacion_whatsapp_enviada')->default(false)->after('observaciones_recepcion');
            
            $table->foreign('confirmado_por')->references('id_usuario')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropForeign(['confirmado_por']);
            $table->dropColumn([
                'recepcion_confirmada',
                'fecha_confirmacion_recepcion',
                'confirmado_por',
                'observaciones_recepcion',
                'notificacion_whatsapp_enviada'
            ]);
        });
    }
};