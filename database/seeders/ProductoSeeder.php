<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener tipos
        $tipos = DB::table('tipo_producto')->pluck('id_tipoProducto','nombre');

        $data = [
            ['nombre'=>'Vestido básico',  'id_tipoProducto'=>$tipos['Vestidos'] ?? 1,   'precio_unitario'=>120.00, 'descripcion'=>'Vestido línea A', 'habilitado'=>true],
            ['nombre'=>'Vestido fiesta',  'id_tipoProducto'=>$tipos['Vestidos'] ?? 1,   'precio_unitario'=>250.00, 'descripcion'=>'Con encaje', 'habilitado'=>true],
            ['nombre'=>'Camisa clásica',  'id_tipoProducto'=>$tipos['Camisas'] ?? 1,    'precio_unitario'=>80.00,  'descripcion'=>'Oxford', 'habilitado'=>true],
            ['nombre'=>'Camisa slim',     'id_tipoProducto'=>$tipos['Camisas'] ?? 1,    'precio_unitario'=>95.00,  'descripcion'=>'Slim fit', 'habilitado'=>true],
            ['nombre'=>'Pantalón sastre', 'id_tipoProducto'=>$tipos['Pantalones'] ?? 1, 'precio_unitario'=>160.00, 'descripcion'=>'Tela italiana', 'habilitado'=>true],
        ];

        foreach ($data as $p) {
            DB::table('producto')->updateOrInsert(
                ['nombre' => $p['nombre']],
                $p + ['created_at'=>now(), 'updated_at'=>now()]
            );
        }
    }
}
