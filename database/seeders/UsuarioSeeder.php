<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $now = now();
        $adminId   = Rol::where('nombre', 'Administrador')->value('id_rol') ?? 1;
        $empleadoId= Rol::where('nombre', 'Empleado')->value('id_rol') ?? 2;
        $clienteId = Rol::where('nombre', 'Cliente')->value('id_rol') ?? 3;

        $usuarios = [
            [
                'nombre'   => 'Admin',
                'email'    => 'super@boom.com',
                'id_rol'   => $adminId,
            ],
            [
                'nombre'   => 'Cliente Demo',
                'email'    => 'prueba@correo.com',
                'id_rol'   => $clienteId,
            ],
            [
                'nombre'   => 'Empleado Demo',
                'email'    => 'prueba2@correo.com',
                'id_rol'   => $empleadoId,
            ],
        ];

        foreach ($usuarios as $u) {
            DB::table('usuario')->updateOrInsert(
                ['email' => $u['email']], // clave única
                [
                    'nombre'     => $u['nombre'],
                    'password'   => Hash::make('clave123'),
                    'id_rol'     => $u['id_rol'],
                    'telefono'   => null,
                    'direccion'  => null,
                    'habilitado' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

       
        $clienteUserId = DB::table('usuario')->where('email', 'prueba@correo.com')->value('id_usuario');
        if ($clienteUserId) {
          
            $clienteId = DB::table('clientes')->orderBy('id')->value('id');
            if ($clienteId) {
                DB::table('clientes')->where('id', $clienteId)->update(['id_usuario' => $clienteUserId, 'updated_at' => $now]);
            }
        }
    }
}
