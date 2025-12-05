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
        Schema::table('avance_produccion', function (Blueprint $table) {
            // Campo para el operario que realizó físicamente el trabajo
            $table->unsignedBigInteger('user_id_operario')->nullable()->after('registrado_por');
            
            // Campo para el costo de mano de obra (pago a destajo)
            $table->decimal('costo_mano_obra', 10, 2)->nullable()->after('user_id_operario');
            
            // Foreign key al usuario operario
            $table->foreign('user_id_operario')->references('id_usuario')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avance_produccion', function (Blueprint $table) {
            $table->dropForeign(['user_id_operario']);
            $table->dropColumn(['user_id_operario', 'costo_mano_obra']);
        });
    }
};
