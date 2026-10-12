<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'rol' => 'propietario',
            'remember_token' => Str::random(10),
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

    /**
     * Usuario del gobierno municipal / autoridad ambiental.
     */
    public function gobierno(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => 'admin_gobierno',
        ]);
    }

    /**
     * Inspector ambiental de la cuenca.
     */
    public function inspector(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => 'inspector',
        ]);
    }

    /**
     * Dueño de un establecimiento comercial.
     */
    public function propietario(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => 'propietario',
        ]);
    }
}
