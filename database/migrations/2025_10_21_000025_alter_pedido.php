<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Crear columnas que falten (en modo seguro)
        Schema::table('pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido', 'fecha_pedido'))   $table->date('fecha_pedido')->nullable();
            if (!Schema::hasColumn('pedido', 'fecha_entrega'))  $table->date('fecha_entrega')->nullable();
            if (!Schema::hasColumn('pedido', 'metodo_pago'))    $table->string('metodo_pago', 50)->nullable();
            if (!Schema::hasColumn('pedido', 'observaciones'))  $table->text('observaciones')->nullable();
            if (!Schema::hasColumn('pedido', 'estado'))         $table->string('estado', 50)->nullable();
        });

        // 2) Normalizar total -> total_pedido (renombrar si se puede; si no, crear/copiar/borrar)
        if (Schema::hasColumn('pedido', 'total') && !Schema::hasColumn('pedido', 'total_pedido')) {
            try {
                Schema::table('pedido', function (Blueprint $table) {
                    $table->renameColumn('total', 'total_pedido'); // requiere doctrine/dbal instalado
                });
            } catch (\Throwable $e) {
                // Plan B: crear + copiar + eliminar
                Schema::table('pedido', function (Blueprint $table) {
                    $table->decimal('total_pedido', 12, 2)->nullable();
                });
                DB::statement("UPDATE pedido SET total_pedido = total");
                Schema::table('pedido', function (Blueprint $table) {
                    $table->dropColumn('total');
                });
            }
        } elseif (!Schema::hasColumn('pedido', 'total_pedido')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->decimal('total_pedido', 12, 2)->nullable();
            });
        } else {
            // Asegurar precisión (best-effort)
            try {
                Schema::table('pedido', function (Blueprint $table) {
                    $table->decimal('total_pedido', 12, 2)->nullable()->change();
                });
            } catch (\Throwable $e) { /* ignore */ }
        }

        // 3) Backfill de fecha_pedido y pasarla a NOT NULL si hay filas
        if (Schema::hasColumn('pedido', 'fecha_pedido')) {
            DB::statement("UPDATE pedido SET fecha_pedido = COALESCE(fecha_pedido, created_at::date)");
            try {
                Schema::table('pedido', function (Blueprint $table) {
                    $table->date('fecha_pedido')->nullable(false)->change();
                });
            } catch (\Throwable $e) { /* ignore */ }
        }

        // 4) Asegurar longitudes (best-effort, sin Doctrine)
        try {
            Schema::table('pedido', function (Blueprint $table) {
                if (Schema::hasColumn('pedido', 'metodo_pago')) {
                    $table->string('metodo_pago', 50)->nullable()->change();
                }
                if (Schema::hasColumn('pedido', 'estado')) {
                    $table->string('estado', 50)->nullable()->change();
                }
            });
        } catch (\Throwable $e) { /* ignore */ }

        // 5) FK id_cliente (sin Doctrine, con SQL nativo)
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'pedido_id_cliente_foreign'
                ) THEN
                    ALTER TABLE pedido
                    ADD CONSTRAINT pedido_id_cliente_foreign
                    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE;
                END IF;
            END$$;
        ");

        // 6) Índices id_cliente+fecha_pedido y estado (idempotentes)
        DB::statement("CREATE INDEX IF NOT EXISTS pedido_idx_cliente_fecha ON pedido (id_cliente, fecha_pedido)");
        DB::statement("CREATE INDEX IF NOT EXISTS pedido_idx_estado ON pedido (estado)");
    }

    public function down(): void
    {
        // Limpieza best-effort
        DB::statement("DROP INDEX IF EXISTS pedido_idx_cliente_fecha");
        DB::statement("DROP INDEX IF EXISTS pedido_idx_estado");
        DB::statement("ALTER TABLE pedido DROP CONSTRAINT IF EXISTS pedido_id_cliente_foreign");
        // No tocamos columnas para no perder datos en down()
    }
};
