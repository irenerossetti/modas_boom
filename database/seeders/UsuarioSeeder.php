<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Admin
        $adminEmail = env('ADMIN_EMAIL', 'super@boom.com');
        $adminPassword = env('ADMIN_PASSWORD', 'clave123');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'id_rol' => 1,
                'nombre' => 'Super Admin',
                'password' => bcrypt($adminPassword),
                'habilitado' => 'true',
            ]
        );

        // Usuario Empleado/Trabajador
        User::updateOrCreate(
            ['email' => 'empleado@boom.com'],
            [
                'id_rol' => 2,
                'nombre' => 'Empleado Test',
                'password' => bcrypt('clave123'),
                'habilitado' => 'true',
            ]
        );

        // Usuario Cliente
        User::updateOrCreate(
            ['email' => 'cliente@boom.com'],
            [
                'id_rol' => 3,
                'nombre' => 'Cliente Test',
                'password' => bcrypt('clave123'),
                'habilitado' => 'true',
            ]
        );
    }
}
