<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin puede cambiar la contraseña de un usuario', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    $user = User::factory()->create(['id_rol' => 2, 'password' => bcrypt('oldpassword')]);

    $this->assertTrue(Hash::check('oldpassword', $user->password));

    $response = $this->actingAs($admin)->put(route('users.update', $user->id_usuario), [
        'id_rol' => $user->id_rol,
        'nombre' => $user->nombre,
        'telefono' => $user->telefono,
        'direccion' => $user->direccion,
        'email' => $user->email,
        'password' => 'newsecurepassword',
        'password_confirmation' => 'newsecurepassword',
        'habilitado' => $user->habilitado,
    ]);

    $response->assertRedirect(route('users.index'));
    $user->refresh();
    $this->assertTrue(Hash::check('newsecurepassword', $user->password));
});
