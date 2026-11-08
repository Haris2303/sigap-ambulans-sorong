<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Route;

/**
 * Mendaftarkan route bernama yang belum ada agar view yang memanggil route() dapat dirender
 * sebelum route sungguhan dibuat. Route yang sudah ada tidak diubah.
 */
trait MenyediakanRuteSementara
{
    /** @param array<int, string> $tambahan */
    protected function sediakanRuteSementara(array $tambahan = []): void
    {
        $nama = array_merge(
            ['beranda', 'faskes.index', 'login', 'register', 'logout', 'warga.beranda', 'admin.dashboard', 'petugas.dashboard', 'sopir.dashboard'],
            array_column(array_merge(...array_values(config('sigap.menu'))), 'route'),
            $tambahan,
        );

        foreach (array_unique($nama) as $n) {
            if (! Route::has($n)) {
                Route::any('/_sementara/'.str_replace('.', '/', $n), fn () => 'sementara')->name($n);
            }
        }
        Route::getRoutes()->refreshNameLookups();
    }
}
