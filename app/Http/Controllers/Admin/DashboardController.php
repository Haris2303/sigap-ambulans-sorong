<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\RingkasanDinkes;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(RingkasanDinkes $ringkasan): View
    {
        return view('admin.dashboard', [
            'pesanan' => $ringkasan->pesanan(),
            'ketersediaan' => $ringkasan->ketersediaan(),
            'grafik' => $ringkasan->pesananTujuhHari(),
            'terbaru' => Pesanan::with('pemohon')->latest()->orderByDesc('id')->limit(10)->get(),
        ]);
    }
}
