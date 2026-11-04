<?php

namespace App\Http\Controllers\Publik;

use App\Enums\JenisLayanan;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Ambulans;
use App\Models\Faskes;
use Illuminate\Contracts\View\View;

class BerandaController extends Controller
{
    public function __invoke(): View
    {
        $pengguna = auth()->user();

        // Hanya Masyarakat yang memesan; peran lain diarahkan ke dasbornya.
        if ($pengguna !== null && $pengguna->role !== Role::Warga) {
            $aksi = ['label' => 'Ke dasbor', 'url' => route($pengguna->role->ruteBeranda())];
        } else {
            $aksi = [
                'label' => 'Pesan ambulans',
                'url' => $pengguna !== null ? route('warga.pesanan.buat') : route('login'),
            ];
        }

        $statistik = [
            'faskes' => Faskes::where('aktif', true)->count(),
            'ambulans' => Ambulans::count(),
            'layanan' => count(JenisLayanan::cases()),
        ];

        return view('publik.beranda', ['aksiUtama' => $aksi, 'statistik' => $statistik]);
    }
}
