<?php

namespace App\Support;

/**
 * Kontak telepon darurat Dinkes dari config('sigap.kontak_darurat'). Tanpa nomor (belum diatur di .env) tidak ada yang ditampilkan:
 * aplikasi tidak pernah mengisi nomor sendiri.
 */
final class KontakDarurat
{
    /** @return array{nama: string, nomor: string, tel: string}|null null bila nomor belum diatur atau tidak berbentuk nomor telepon */
    public static function data(): ?array
    {
        $nomor = trim((string) config('sigap.kontak_darurat.nomor'));
        if ($nomor === '') {
            return null;
        }

        // Hanya angka (dan "+" di depan) untuk tautan tel:; teks yang ditampilkan tetap seperti yang ditulis Dinkes.
        $angka = preg_replace('/\D/', '', $nomor) ?? '';
        if (strlen($angka) < 3) {
            return null;
        }

        return [
            'nama' => trim((string) config('sigap.kontak_darurat.nama')) ?: 'Dinkes',
            'nomor' => $nomor,
            'tel' => (str_starts_with($nomor, '+') ? '+' : '').$angka,
        ];
    }
}
