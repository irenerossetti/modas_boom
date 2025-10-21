<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Insert básicos
        $ids = [];
        $ids['Vestidos'] = DB::table('tipo_producto')->insertGetId([
            'nombre' => 'Vestidos',
            'descripcion' => 'Vestidos casuales y de fiesta',
            'dificultad_produccion' => 'Media',
            'habilitado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_tipoProducto');

        $ids['Camisas'] = DB::table('tipo_producto')->insertGetId([
            'nombre' => 'Camisas',
            'descripcion' => 'Camisas a medida',
            'dificultad_produccion' => 'Baja',
            'habilitado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_tipoProducto');

        $ids['Pantalones'] = DB::table('tipo_producto')->insertGetId([
            'nombre' => 'Pantalones',
            'descripcion' => 'Pantalones sastre',
            'dificultad_produccion' => 'Media',
            'habilitado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_tipoProducto');


      
        DB::statement("UPDATE tipo_producto SET tiempo_produccion = interval '2 hours' WHERE nombre = 'Vestidos'");
        DB::statement("UPDATE tipo_producto SET tiempo_produccion = interval '1 hour' WHERE nombre = 'Camisas'");
        DB::statement("UPDATE tipo_producto SET tiempo_produccion = interval '1.5 hours' WHERE nombre = 'Pantalones'");
    }
}
