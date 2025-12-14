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
        Schema::create('presupuestos_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_prenda');
            $table->string('tipo_tela');
            $table->text('descripcion')->nullable();
            
            // Costos de materiales
            $table->decimal('costo_tela', 10, 2)->default(0);
            $table->decimal('costo_cierre', 10, 2)->default(0);
            $table->decimal('costo_boton', 10, 2)->default(0);
            $table->decimal('costo_bolsa', 10, 2)->default(0);
            $table->decimal('costo_hilo', 10, 2)->default(0);
            $table->decimal('costo_etiqueta_cinta', 10, 2)->default(0);
            $table->decimal('costo_etiqueta_carton', 10, 2)->default(0);
            
            // Costos de mano de obra
            $table->decimal('costo_tallerista', 10, 2)->default(0);
            $table->decimal('costo_planchado', 10, 2)->default(0);
            $table->decimal('costo_ayudante', 10, 2)->default(0);
            $table->decimal('costo_cortador', 10, 2)->default(0);
            
            // Totales
            $table->decimal('total_materiales', 10, 2)->default(0);
            $table->decimal('total_mano_obra', 10, 2)->default(0);
            $table->decimal('costo_total', 10, 2)->default(0);
            
            // Relaciones
            $table->unsignedBigInteger('id_usuario_registro');
            $table->unsignedBigInteger('id_pedido')->nullable(); // Opcional, si está asociado a un pedido
            
            // Estado del presupuesto
            $table->enum('estado', ['Borrador', 'Aprobado', 'Utilizado'])->default('Borrador');
            
            $table->timestamps();
            
            // Índices y claves foráneas
            $table->foreign('id_usuario_registro')->references('id_usuario')->on('usuario');
            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('set null');
            
            // Índices para búsquedas
            $table->index(['tipo_prenda', 'tipo_tela']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_produccion');
    }
};