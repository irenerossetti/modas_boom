<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BitacoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes
        $usuarios = \App\Models\User::all();
        
        if ($usuarios->isEmpty()) {
            $this->command->info('No hay usuarios en el sistema. Creando registros de sistema...');
            $usuarioId = null;
        } else {
            $usuarioId = $usuarios->first()->id_usuario;
        }

        // Crear registros de prueba
        $registros = [
            [
                'id_usuario' => $usuarioId,
                'accion' => 'LOGIN',
                'modulo' => 'AUTH',
                'descripcion' => 'Usuario inició sesión en el sistema',
                'datos_nuevos' => ['usuario_id' => $usuarioId, 'timestamp' => now()],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subHours(2),
            ],
            [
                'id_usuario' => $usuarioId,
                'accion' => 'VIEW',
                'modulo' => 'CLIENTES',
                'descripcion' => 'Consultó lista de clientes',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subHours(1),
            ],
            [
                'id_usuario' => $usuarioId,
                'accion' => 'CREATE',
                'modulo' => 'CLIENTES',
                'descripcion' => 'Creó un nuevo cliente',
                'datos_nuevos' => [
                    'nombre' => 'Juan',
                    'apellido' => 'Pérez',
                    'ci_nit' => '12345678',
                    'telefono' => '70123456',
                    'email' => 'juan@example.com'
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subMinutes(30),
            ],
            [
                'id_usuario' => $usuarioId,
                'accion' => 'UPDATE',
                'modulo' => 'CLIENTES',
                'descripcion' => 'Actualizó datos de cliente',
                'datos_anteriores' => [
                    'telefono' => '70123456',
                    'email' => 'juan@example.com'
                ],
                'datos_nuevos' => [
                    'telefono' => '70987654',
                    'email' => 'juan.perez@example.com'
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subMinutes(15),
            ],
            [
                'id_usuario' => $usuarioId,
                'accion' => 'VIEW',
                'modulo' => 'USUARIOS',
                'descripcion' => 'Consultó lista de usuarios',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subMinutes(5),
            ],
        ];

        foreach ($registros as $registro) {
            \App\Models\Bitacora::create($registro);
        }

        $this->command->info('Se crearon ' . count($registros) . ' registros de bitácora de prueba.');
    }
}
