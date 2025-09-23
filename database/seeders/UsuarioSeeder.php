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
    \App\Models\Usuario::create([
        'id_rol' => 1, // ID del rol Administrador
        'nombre' => 'Irene Rossetti', // O tu nombre
        'email' => 'admin@boom.com',
        'password' => bcrypt('password123'), // Contraseña temporal
    ]);
    }
}
