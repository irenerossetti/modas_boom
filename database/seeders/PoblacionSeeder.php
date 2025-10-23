<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Database\Seeder;

class PoblacionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 5 empleados (usuarios con rol Empleado, id_rol = 2)
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'id_rol' => 2, // Empleado
                'nombre' => "Empleado {$i}",
                'telefono' => fake()->phoneNumber(),
                'direccion' => fake()->address(),
                'email' => "empleado{$i}@boom.com",
                'password' => bcrypt('password123'),
                'habilitado' => true,
            ]);
        }

        // Crear 22 clientes (usuarios con rol Cliente y registro en clientes)
        for ($i = 1; $i <= 22; $i++) {
            $user = User::create([
                'id_rol' => 3, // Cliente
                'nombre' => fake()->name(),
                'telefono' => fake()->phoneNumber(),
                'direccion' => fake()->address(),
                'email' => "cliente{$i}@boom.com",
                'password' => bcrypt('password123'),
                'habilitado' => true,
            ]);

            // Crear cliente asociado
            Cliente::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre,
                'apellido' => fake()->lastName(),
                'ci_nit' => fake()->unique()->numerify('########'),
                'telefono' => $user->telefono,
                'email' => $user->email,
                'direccion' => $user->direccion,
            ]);
        }
    }
}