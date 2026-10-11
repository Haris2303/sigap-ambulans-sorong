<?php

namespace Database\Factories;

use App\Enums\JenisFaskes;
use App\Models\Faskes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faskes>
 */
class FaskesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => 'Faskes '.fake()->unique()->numerify('####'),
            'jenis' => JenisFaskes::Puskesmas,
            'aktif' => true,
        ];
    }

    public function rs(): static
    {
        return $this->state(['jenis' => JenisFaskes::Rs]);
    }

    public function puskesmas(): static
    {
        return $this->state(['jenis' => JenisFaskes::Puskesmas]);
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }

    public function koordinat(float $lat = -0.8762, float $lng = 131.2558): static
    {
        return $this->state(['lat' => $lat, 'lng' => $lng]);
    }

    public function diperbarui(int $igd = 5, int $rawatInap = 10): static
    {
        return $this->state([
            'bed_igd_total' => $igd + 2,
            'bed_igd_tersedia' => $igd,
            'bed_rawat_inap_total' => $rawatInap + 5,
            'bed_rawat_inap_tersedia' => $rawatInap,
            'dokter_umum_tersedia' => 2,
            'dokter_spesialis_tersedia' => 1,
            'ketersediaan_diperbarui_pada' => now(),
        ]);
    }
}
