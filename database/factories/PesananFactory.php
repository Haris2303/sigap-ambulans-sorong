<?php

namespace Database\Factories;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\TingkatDarurat;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_pasien' => fake()->name(),
            'no_telepon_kontak' => '08'.fake()->numerify('##########'),
            'jenis_layanan' => JenisLayanan::Darurat,
            'tingkat_darurat' => TingkatDarurat::Biasa,
            'lokasi_jemput' => 'Jalan Ahmad Yani, Kota Sorong',
            'status' => PesananStatus::Menunggu,
        ];
    }

    /** Kode dibuat dari id setelah baris tersimpan, sama seperti PesananWorkflow::buat. */
    public function configure(): static
    {
        return $this->afterCreating(function (Pesanan $pesanan) {
            if ($pesanan->kode === null) {
                $pesanan->forceFill(['kode' => 'SGP-'.str_pad((string) $pesanan->id, 6, '0', STR_PAD_LEFT)])->save();
            }
        });
    }

    public function berstatus(PesananStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function gawat(): static
    {
        return $this->state(['tingkat_darurat' => TingkatDarurat::Gawat]);
    }
}
