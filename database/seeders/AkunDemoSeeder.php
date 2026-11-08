<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AkunDemoSeeder extends Seeder
{
    /** Domain akun demo. */
    public const DOMAIN = '@sigap.test';

    /** @var array<string, string> slug => nama Faskes tempat petugas bertugas */
    public const PETUGAS = [
        'selebesolu' => 'RSUD Selebesolu',
        'klasaman' => 'Puskesmas Klasaman',
    ];

    /** @var array<string, string> slug => nama Faskes asal sopir (Faskes ber-ambulans darurat, Klasaman, Remu) */
    public const SOPIR = [
        'selebesolu' => 'RSUD Selebesolu',
        'al' => 'RS AL',
        'aryoko' => 'RS Aryoko',
        'posmor' => 'Klinik Posmor 3 Km 16',
        'klasaman' => 'Puskesmas Klasaman',
        'remu' => 'Puskesmas Remu',
    ];

    private ?string $sandi = null;

    public function run(): void
    {
        $this->buat('Admin Dinkes', 'admin', Role::Admin);
        $this->buat('Warga Demo', 'warga', Role::Warga);

        foreach (self::PETUGAS as $slug => $namaFaskes) {
            $faskes = Faskes::where('nama', $namaFaskes)->first();
            $this->buat('Petugas '.$namaFaskes, 'petugas.'.$slug, Role::PetugasFaskes, $faskes);
        }

        foreach (self::SOPIR as $slug => $namaFaskes) {
            $faskes = Faskes::where('nama', $namaFaskes)->first();
            $sopir = $this->buat('Sopir '.$namaFaskes, 'sopir.'.$slug, Role::Sopir, $faskes);

            $ambulans = $faskes === null ? null : Ambulans::where('faskes_id', $faskes->id)->orderBy('id')->first();
            if ($ambulans !== null && $ambulans->sopir_id === null) {
                $ambulans->update(['sopir_id' => $sopir->id]);
            }
        }
    }

    private function buat(string $nama, string $lokal, Role $peran, ?Faskes $faskes = null): User
    {
        return User::firstOrCreate(
            ['email' => $lokal.self::DOMAIN],
            [
                'name' => $nama,
                'password' => $this->sandi(),
                'role' => $peran,
                'aktif' => true,
                'faskes_id' => $faskes?->id,
                'no_telepon' => '0811000'.str_pad((string) (crc32($lokal) % 10000), 4, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
            ],
        );
    }

    private function sandi(): string
    {
        if ($this->sandi !== null) {
            return $this->sandi;
        }

        $dariConfig = config('sigap.seed_password');
        if (is_string($dariConfig) && $dariConfig !== '') {
            return $this->sandi = $dariConfig;
        }

        if (app()->environment('local', 'testing', 'shot')) {
            return $this->sandi = 'password';
        }

        $this->sandi = Str::random(16);
        $this->command?->warn('Sandi akun demo (dicetak sekali): '.$this->sandi);

        return $this->sandi;
    }
}
