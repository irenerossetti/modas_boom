public function run(): void
{
    // Usuario Admin
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

    // Usuario Cliente
    \App\Models\User::firstOrCreate(
        ['email' => 'prueba@correo.com'],
        [
            'id_rol' => 2, // Asumiendo que 2 es cliente
            'nombre' => 'Cliente Test',
            'password' => bcrypt('clave123'),
            'habilitado' => true,
        ]
    );

    // Usuario Empleado
    \App\Models\User::firstOrCreate(
        ['email' => 'prueba2@correo.com'],
        [
            'id_rol' => 3, // Asumiendo que 3 es empleado
            'nombre' => 'Empleado Test', 
            'password' => bcrypt('clave123'),
            'habilitado' => true,
        ]
    );
}