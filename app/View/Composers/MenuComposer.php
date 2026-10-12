<?php

namespace App\View\Composers;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Pesanan;
use Illuminate\View\View;

/**
 * Angka lencana menu rel dan titik lonceng; query tidak boleh ada di Blade. Dipasang pada bilah samping/laci dan
 * kepala halaman, yang masing-masing merender sekali per halaman sehingga hitungannya tetap satu query ringan per
 * tempat (bukan N+1 terhadap koleksi).
 */
class MenuComposer
{
    public function compose(View $view): void
    {
        $pengguna = auth()->user();
        $lencana = [];

        if ($pengguna?->role === Role::Admin) {
            $lencana['pesanan_menunggu'] = Pesanan::berstatus(PesananStatus::Menunggu)->count();
        }

        $view->with('lencanaMenu', $lencana);
    }
}
