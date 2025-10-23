<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_usuario' => User::factory(),
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'ci_nit' => $this->faker->unique()->numerify('########'),
            'telefono' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'direccion' => $this->faker->optional()->address(),
        ];
    }
}
