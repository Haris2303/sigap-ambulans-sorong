<?php

namespace App\Services;

use App\Enums\PesananStatus;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;

/** Angka ringkasan untuk dasbor dan polling Dinkes (satu sumber). */
class RingkasanDinkes
{
    /**
     * Dua query: hitungan per status dan selesai hari ini (awal hari menurut zona waktu aplikasi).
     *
     * @return array<string, int> kunci sama dengan data-ringkasan/data-lencana di view
     */
    public function pesanan(): array
    {
        $perStatus = Pesanan::query()->selectRaw('status, count(*) as jumlah')->groupBy('status')->toBase()->pluck('jumlah', 'status')->all();
        $hitung = fn (PesananStatus ...$status) => array_sum(array_map(fn (PesananStatus $s) => (int) ($perStatus[$s->value] ?? 0), $status));

        return [
            'pesanan_menunggu' => $hitung(PesananStatus::Menunggu),
            'pesanan_terverifikasi' => $hitung(PesananStatus::Terverifikasi),
            'pesanan_berjalan' => $hitung(...array_filter(PesananStatus::cases(), fn (PesananStatus $s) => $s->memegangSumberDaya())),
            'pesanan_selesai_hari_ini' => Pesanan::berstatus(PesananStatus::Selesai)
                ->where('selesai_pada', '>=', now()->startOfDay())->count(),
        ];
    }

    /**
     * Dua query: jumlah ambulans tersedia dan jumlah sumber daya Faskes aktif dalam satu agregat.
     *
     * @return array<string, int>
     */
    public function ketersediaan(): array
    {
        $faskes = Faskes::aktif()->toBase()->selectRaw(
            'coalesce(sum(bed_igd_tersedia), 0) as bed_igd, coalesce(sum(bed_rawat_inap_tersedia), 0) as bed_rawat_inap, '
            .'coalesce(sum(dokter_umum_tersedia), 0) as dokter_umum, coalesce(sum(dokter_spesialis_tersedia), 0) as dokter_spesialis'
        )->first();

        return [
            'ambulans_tersedia' => Ambulans::tersedia()->whereHas('faskes', fn ($q) => $q->where('aktif', true))->count(),
            'bed_igd_tersedia' => (int) $faskes->bed_igd,
            'bed_rawat_inap_tersedia' => (int) $faskes->bed_rawat_inap,
            'dokter_tersedia' => (int) $faskes->dokter_umum + (int) $faskes->dokter_spesialis,
        ];
    }
}
