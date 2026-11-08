<?php

namespace Database\Factories;

use App\Enums\JenisAmbulans;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ambulans>
 */
class AmbulansFactory extends Factory
{
    public function definition(): array
    {
        return [
            'faskes_id' => Faskes::factory(),
            'plat_nomor' => 'PB-'.fake()->unique()->numerify('####').'-XX',
            'jenis' => JenisAmbulans::Transport,
            'status' => StatusAmbulans::Tersedia,
        ];
    }

    public function darurat(): static
    {
        return $this->state(['jenis' => JenisAmbulans::Darurat]);
    }

    public function servis(): static
    {
        return $this->state(['status' => StatusAmbulans::Servis]);
    }

    public function bertugas(): static
    {
        return $this->state(['status' => StatusAmbulans::Bertugas]);
    }
}
