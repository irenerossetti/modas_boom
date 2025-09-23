<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol; // No te olvides de importar el modelo

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // Usamos firstOrCreate para evitar errores si ya existen
     Rol::firstOrCreate(['id_rol' => 1], ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema']);
     Rol::firstOrCreate(['id_rol' => 2], ['nombre' => 'Empleado', 'descripcion' => 'Acceso limitado para gestión']);
     Rol::firstOrCreate(['id_rol' => 3], ['nombre' => 'Cliente', 'descripcion' => 'Acceso al portal de clientes']);
    }
}