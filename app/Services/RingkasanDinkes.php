<?php

namespace App\Services;

use App\Enums\PesananStatus;
use App\Enums\StatusAmbulans;
use App\Enums\TingkatDarurat;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Support\WaktuTunggu;
use Illuminate\Support\Carbon;

/** Angka ringkasan untuk dasbor dan polling Dinkes (satu sumber). */
class RingkasanDinkes
{
    /**
     * Dua query: hitungan per status (beserta jumlah Gawat dan pesanan menunggu tertua per status dalam query yang
     * sama) dan selesai hari ini (awal hari menurut zona waktu aplikasi).
     *
     * @return array<string, int> kunci sama dengan data-ringkasan/data-lencana/data-angka di view
     */
    public function pesanan(): array
    {
        $baris = Pesanan::query()
            ->selectRaw('status, count(*) as jumlah, sum(case when tingkat_darurat = ? then 1 else 0 end) as gawat, min(created_at) as tertua', [TingkatDarurat::Gawat->value])
            ->groupBy('status')->toBase()->get()->keyBy('status');
        $perStatus = $baris->pluck('jumlah', 'status')->all();
        $hitung = fn (PesananStatus ...$status) => array_sum(array_map(fn (PesananStatus $s) => (int) ($perStatus[$s->value] ?? 0), $status));
        $tertuaMenunggu = $baris[PesananStatus::Menunggu->value]->tertua ?? null;

        return [
            'pesanan_menunggu' => $hitung(PesananStatus::Menunggu),
            'pesanan_menunggu_gawat' => (int) ($baris[PesananStatus::Menunggu->value]->gawat ?? 0),
            'pesanan_menunggu_tertua_menit' => $tertuaMenunggu ? WaktuTunggu::menit(Carbon::parse($tertuaMenunggu)) : 0,
            'pesanan_terverifikasi' => $hitung(PesananStatus::Terverifikasi),
            'pesanan_berjalan' => $hitung(...array_filter(PesananStatus::cases(), fn (PesananStatus $s) => $s->memegangSumberDaya())),
            'pesanan_selesai_hari_ini' => Pesanan::berstatus(PesananStatus::Selesai)
                ->where('selesai_pada', '>=', now()->startOfDay())->count(),
        ];
    }

    /**
     * Dua query: ambulans (total dan tersedia, Faskes aktif saja) dan satu agregat Faskes dengan sum bersyarat untuk
     * memisahkan "tersedia" (Faskes aktif saja) dari "total" (semua Faskes, termasuk nonaktif).
     *
     * Dokter tidak punya kolom kapasitas (docs/RANCANGAN_ALUR.md). "Total" di sini berarti jumlah dokter tercatat
     * tersedia di seluruh Faskes termasuk nonaktif, dibandingkan dengan "tersedia" yang hanya Faskes aktif. Ini bukan
     * kapasitas roster, karena kolomnya memang tidak ada. Dicatat juga di docs/PROGRESS.md.
     *
     * @return array<string, int>
     */
    public function ketersediaan(): array
    {
        $ambulans = Ambulans::query()->whereHas('faskes', fn ($q) => $q->where('aktif', true))
            ->selectRaw('count(*) as total, sum(case when status = ? then 1 else 0 end) as tersedia', [StatusAmbulans::Tersedia->value])
            ->toBase()->first();

        $faskes = Faskes::query()->toBase()->selectRaw(
            'coalesce(sum(case when aktif then bed_igd_tersedia else 0 end), 0) as bed_igd, coalesce(sum(bed_igd_total), 0) as bed_igd_total, '
            .'coalesce(sum(case when aktif then bed_rawat_inap_tersedia else 0 end), 0) as bed_rawat_inap, coalesce(sum(bed_rawat_inap_total), 0) as bed_rawat_inap_total, '
            .'coalesce(sum(case when aktif then dokter_umum_tersedia + dokter_spesialis_tersedia else 0 end), 0) as dokter, '
            .'coalesce(sum(dokter_umum_tersedia + dokter_spesialis_tersedia), 0) as dokter_total'
        )->first();

        return [
            'ambulans_tersedia' => (int) ($ambulans->tersedia ?? 0),
            'ambulans_total' => (int) ($ambulans->total ?? 0),
            'bed_igd_tersedia' => (int) $faskes->bed_igd,
            'bed_igd_total' => (int) $faskes->bed_igd_total,
            'bed_rawat_inap_tersedia' => (int) $faskes->bed_rawat_inap,
            'bed_rawat_inap_total' => (int) $faskes->bed_rawat_inap_total,
            'dokter_tersedia' => (int) $faskes->dokter,
            'dokter_total' => (int) $faskes->dokter_total,
        ];
    }
}
