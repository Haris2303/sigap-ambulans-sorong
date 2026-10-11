<?php

namespace App\Http\Controllers\Warga;

use App\Enums\PesananStatus;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Contracts\View\View;

class BerandaController extends Controller
{
    public function __invoke(): View
    {
        $pesanan = Pesanan::where('user_id', auth()->id());

        return view('warga.beranda', [
            'aktif' => (clone $pesanan)->aktif()->latest()->get(),
            'jumlahSelesai' => (clone $pesanan)->berstatus(PesananStatus::Selesai)->count(),
            'jumlahSemua' => (clone $pesanan)->count(),
        ]);
    }
}
