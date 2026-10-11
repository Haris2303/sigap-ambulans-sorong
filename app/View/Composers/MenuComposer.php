<?php

namespace App\View\Composers;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Pesanan;
use Illuminate\View\View;

/** Angka lencana menu rel; query tidak boleh ada di Blade. */
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
