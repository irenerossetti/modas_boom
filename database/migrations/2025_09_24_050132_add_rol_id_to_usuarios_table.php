<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // tu tabla real es "usuario" (singular, según el error)
        if (!Schema::hasColumn('usuario', 'id_rol')) {
            Schema::table('usuario', function (Blueprint $table) {
                // En Postgres, foreignId funciona bien
                $table->foreignId('id_rol')->nullable()
                      ->constrained('roles')     // referencia a roles.id
                      ->nullOnDelete()
                      ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuario', 'id_rol')) {
            Schema::table('usuario', function (Blueprint $table) {
                // nombre por convención: usuario_id_rol_foreign
                $table->dropForeign(['id_rol']);
                $table->dropColumn('id_rol');
            });
        }
    }
};
