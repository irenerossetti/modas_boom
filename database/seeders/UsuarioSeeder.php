<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin solo si no existe, leyendo credenciales de .env para mayor seguridad
        $adminEmail = env('ADMIN_EMAIL', 'super@boom.com');
        $adminPassword = env('ADMIN_PASSWORD', 'clave123');

        \App\Models\User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'id_rol' => 1,
                'nombre' => 'Super Admin',
                'password' => bcrypt($adminPassword),
                'habilitado' => true,
            ]
        );
    }
}
