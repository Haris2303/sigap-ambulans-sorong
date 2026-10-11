<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Faskes;
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::Warga,
            'aktif' => true,
            'no_telepon' => '08'.fake()->numerify('##########'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function warga(): static
    {
        return $this->state(['role' => Role::Warga]);
    }

    public function admin(): static
    {
        return $this->state(['role' => Role::Admin]);
    }

    public function petugas(?Faskes $faskes = null): static
    {
        return $this->state(fn () => [
            'role' => Role::PetugasFaskes,
            'faskes_id' => $faskes?->id ?? Faskes::factory(),
        ]);
    }

    public function sopir(?Faskes $faskes = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Sopir,
            'faskes_id' => $faskes?->id ?? Faskes::factory(),
            'sedang_online' => false,
        ]);
    }

    public function online(): static
    {
        return $this->state(['sedang_online' => true]);
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }
}
