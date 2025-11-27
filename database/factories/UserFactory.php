<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Rol;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Asegurarnos que existan los roles básicos en todas las ejecuciones de factory
        Rol::firstOrCreate(['nombre' => 'Administrador'], ['habilitado' => true]);
        Rol::firstOrCreate(['nombre' => 'Empleado'], ['habilitado' => true]);

        return [
            'id_rol' => function () {
                // Asegurarnos de tener un rol por defecto 'Empleado' cuando se usan factories
                $rol = Rol::firstOrCreate(['nombre' => 'Empleado'], ['habilitado' => true]);
                return $rol->id_rol;
            },
            'nombre' => fake()->name(),
            'telefono' => fake()->optional()->phoneNumber(),
            'direccion' => fake()->optional()->address(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'habilitado' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
