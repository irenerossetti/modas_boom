<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar a los seeders en orden
        $this->call([
            RolSeeder::class,
            UsuarioSeeder::class,
            PoblacionSeeder::class,
            // ClienteSeeder::class, // Removido
        ]);
    }
}
