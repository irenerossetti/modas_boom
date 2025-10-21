<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nombre'=>'Ana','apellido'=>'Guzmán','ci_nit'=>'1234567','telefono'=>'70000001','email'=>'ana@test.com','direccion'=>'Av. 6 de Agosto #100'],
            ['nombre'=>'Bruno','apellido'=>'Pérez','ci_nit'=>'2234567','telefono'=>'70000002','email'=>'bruno@test.com','direccion'=>'C. Illampu 321'],
            ['nombre'=>'Cecilia','apellido'=>'Rojas','ci_nit'=>'3234567','telefono'=>'70000003','email'=>'ceci@test.com','direccion'=>'Av. Arce 555'],
            ['nombre'=>'Diego','apellido'=>'López','ci_nit'=>'4234567','telefono'=>'70000004','email'=>'diego@test.com','direccion'=>'C. Murillo 18'],
            ['nombre'=>'Elena','apellido'=>'Torrez','ci_nit'=>'5234567','telefono'=>'70000005','email'=>'elena@test.com','direccion'=>'Zona Calacoto'],
        ];

        foreach ($data as $c) {
            Cliente::updateOrCreate(['ci_nit'=>$c['ci_nit']], $c);
        }
    }
}
