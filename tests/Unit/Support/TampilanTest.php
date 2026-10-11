<?php

namespace Tests\Unit\Support;

use App\Support\Tampilan;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TampilanTest extends TestCase
{
    public static function namaDanInisial(): array
    {
        return [
            'dua kata' => ['Admin Dinkes', 'AD'],
            'huruf kecil menjadi kapital' => ['budi santoso', 'BS'],
            'tiga kata: dua pertama' => ['Siti Nur Aini', 'SN'],
            'satu kata' => ['Madonna', 'M'],
            'spasi berlebih' => ['   dua    spasi  ', 'DS'],
            'tab dan baris baru' => ["Rina\tAyu\nLestari", 'RA'],
            'huruf beraksen' => ['émile zola', 'ÉZ'],
            'tanda hubung tetap satu kata' => ['Anne-Marie Lopez', 'AL'],
            'kosong' => ['', '?'],
            'hanya spasi' => ['    ', '?'],
        ];
    }

    #[DataProvider('namaDanInisial')]
    public function test_inisial_dari_dua_kata_pertama(string $nama, string $diharapkan): void
    {
        $this->assertSame($diharapkan, Tampilan::inisial($nama));
    }

    public function test_inisial_maksimum_dua_huruf(): void
    {
        $this->assertLessThanOrEqual(2, mb_strlen(Tampilan::inisial('Satu Dua Tiga Empat Lima')));
    }

    public static function nadaChip(): array
    {
        return [
            'primary' => ['primary', 'bg-primary-container text-on-primary-container'],
            'warning' => ['warning', 'bg-warning-container text-on-warning-container'],
            'success' => ['success', 'bg-success-container text-on-success-container'],
            'error' => ['error', 'bg-error-container text-on-error-container'],
            'tertiary' => ['tertiary', 'bg-tertiary-container text-on-tertiary-container'],
            'neutral' => ['neutral', 'bg-secondary-container text-on-secondary-container'],
        ];
    }

    #[DataProvider('nadaChip')]
    public function test_kelas_chip_menurut_nada(string $tone, string $kelas): void
    {
        $this->assertSame($kelas, Tampilan::kelasChip($tone));
    }

    public function test_kelas_chip_menolak_nada_di_luar_enam_pasangan(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('primary, warning, success, error, tertiary, neutral');

        Tampilan::kelasChip('ungu');
    }

    public function test_kelas_waktu_tunggu_sama_dengan_kelas_lembut_untuk_tiga_nada(): void
    {
        $dari = Tampilan::WAKTU_TUNGGU;

        foreach (['netral', 'peringatan', 'bahaya'] as $nada) {
            foreach (explode(' ', Tampilan::kelasLembut($nada)) as $kelas) {
                $this->assertStringContainsString('data-[nada='.$nada.']:'.$kelas, $dari, $nada.': '.$kelas);
            }
        }
        // Hanya tiga nada itu: tidak ada kelas lain yang menyelinap.
        $this->assertSame(6, count(explode(' ', $dari)));
    }

    public static function namaUntukWarnaAvatar(): array
    {
        return [
            'primary (crc32 % 5 == 0)' => ['Admin Dinkes', 'bg-primary-container text-on-primary-container'],
            'error' => ['Maria Kambu', 'bg-error-container text-on-error-container'],
            'success' => ['budi', 'bg-success-container text-on-success-container'],
            'tertiary' => ['Siti Aminah', 'bg-tertiary-container text-on-tertiary-container'],
        ];
    }

    #[DataProvider('namaUntukWarnaAvatar')]
    public function test_warna_avatar_dari_hash_nama(string $nama, string $kelas): void
    {
        $this->assertSame($kelas, Tampilan::warnaAvatar($nama));
    }

    public function test_warna_avatar_sama_untuk_nama_yang_sama(): void
    {
        $this->assertSame(Tampilan::warnaAvatar('Budi Santoso'), Tampilan::warnaAvatar('Budi Santoso'));
    }
}
