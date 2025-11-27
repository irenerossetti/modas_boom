<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('telas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('stock', 10, 2)->default(0); // metros/rollos as decimal
            $table->string('unidad')->default('m');
            $table->decimal('stock_minimo', 10, 2)->default(0); // threshold
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('telas');
    }
};
