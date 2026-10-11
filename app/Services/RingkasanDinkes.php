<?php

namespace App\Services;

use App\Enums\PesananStatus;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use Illuminate\Support\Carbon;

/** Angka ringkasan untuk dasbor dan polling Dinkes (satu sumber). */
class RingkasanDinkes
{
    /** @return array<string, int> kunci sama dengan data-ringkasan/data-lencana di view */
    public function pesanan(): array
    {
        return [
            'pesanan_menunggu' => Pesanan::berstatus(PesananStatus::Menunggu)->count(),
            'pesanan_terverifikasi' => Pesanan::berstatus(PesananStatus::Terverifikasi)->count(),
            'pesanan_berjalan' => Pesanan::berstatus(
                PesananStatus::Ditugaskan, PesananStatus::Diterima, PesananStatus::DalamPerjalanan
            )->count(),
            'pesanan_selesai_hari_ini' => Pesanan::berstatus(PesananStatus::Selesai)
                ->where('selesai_pada', '>=', now()->startOfDay())->count(),
        ];
    }

    /** @return array<string, int> */
    public function ketersediaan(): array
    {
        $faskes = Faskes::aktif();

        return [
            'ambulans_tersedia' => Ambulans::tersedia()->whereHas('faskes', fn ($q) => $q->where('aktif', true))->count(),
            'bed_igd_tersedia' => (int) (clone $faskes)->sum('bed_igd_tersedia'),
            'bed_rawat_inap_tersedia' => (int) (clone $faskes)->sum('bed_rawat_inap_tersedia'),
            'dokter_tersedia' => (int) (clone $faskes)->sum('dokter_umum_tersedia') + (int) (clone $faskes)->sum('dokter_spesialis_tersedia'),
        ];
    }

    /** @return array<string, int> label hari (mis. "Sen 05/10") => jumlah pesanan dibuat, 7 hari terakhir */
    public function pesananTujuhHari(): array
    {
        $awal = now()->subDays(6)->startOfDay();
        $hitung = Pesanan::where('created_at', '>=', $awal)->pluck('created_at')
            ->countBy(fn (Carbon $t) => $t->toDateString());

        $hasil = [];
        for ($i = 0; $i < 7; $i++) {
            $hari = $awal->copy()->addDays($i);
            $hasil[$hari->translatedFormat('D d/m')] = (int) ($hitung[$hari->toDateString()] ?? 0);
        }

        return $hasil;
    }
}
