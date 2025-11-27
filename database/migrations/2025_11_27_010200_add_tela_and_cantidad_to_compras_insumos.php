<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('compras_insumos', function (Blueprint $table) {
            $table->unsignedBigInteger('tela_id')->nullable()->after('descripcion');
            $table->decimal('cantidad', 12, 2)->default(0)->after('tela_id');
            $table->foreign('tela_id')->references('id')->on('telas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('compras_insumos', function (Blueprint $table) {
            $table->dropForeign(['tela_id']);
            $table->dropColumn('tela_id');
            $table->dropColumn('cantidad');
        });
    }
};
