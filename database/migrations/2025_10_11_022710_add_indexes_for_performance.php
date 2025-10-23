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
        // Verificar si las tablas existen antes de agregar índices
        if (Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table) {
                if (!Schema::hasIndex('pedidos', 'idx_pedidos_cliente')) {
                    $table->index('id_cliente', 'idx_pedidos_cliente');
                }
                if (!Schema::hasIndex('pedidos', 'idx_pedidos_estado')) {
                    $table->index('estado', 'idx_pedidos_estado');
                }
                if (!Schema::hasIndex('pedidos', 'idx_pedidos_created_at')) {
                    $table->index('created_at', 'idx_pedidos_created_at');
                }
            });
        }

        if (Schema::hasTable('bitacora')) {
            Schema::table('bitacora', function (Blueprint $table) {
                if (!Schema::hasIndex('bitacora', 'idx_bitacora_modulo')) {
                    $table->index('modulo', 'idx_bitacora_modulo');
                }
                if (!Schema::hasIndex('bitacora', 'idx_bitacora_created_at')) {
                    $table->index('created_at', 'idx_bitacora_created_at');
                }
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasIndex('clientes', 'idx_clientes_nombre')) {
                    $table->index('nombre', 'idx_clientes_nombre');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices si existen
        if (Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_pedidos_cliente');
                    $table->dropIndex('idx_pedidos_estado');
                    $table->dropIndex('idx_pedidos_created_at');
                } catch (\Exception $e) {
                    // Ignorar errores si los índices no existen
                }
            });
        }

        if (Schema::hasTable('bitacora')) {
            Schema::table('bitacora', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_bitacora_modulo');
                    $table->dropIndex('idx_bitacora_created_at');
                } catch (\Exception $e) {
                    // Ignorar errores si los índices no existen
                }
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_clientes_nombre');
                } catch (\Exception $e) {
                    // Ignorar errores si los índices no existen
                }
            });
        }
    }
};