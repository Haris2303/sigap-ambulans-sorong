<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TabPesanan;
use App\Http\Controllers\Controller;
use App\Services\RingkasanDasbor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RingkasanDasbor $dasbor): View
    {
        $tab = TabPesanan::tryFrom((string) $request->query('tab')) ?? TabPesanan::Semua;
        $data = $dasbor->ambil($tab);

        return view('admin.dashboard', $data + [
            'judulDasbor' => 'Selamat '.$this->sapaan().', '.Str::before($request->user()->name, ' '),
            'subjudulDasbor' => $this->subjudul($data['statistik']['pesanan_menunggu']),
        ]);
    }

    /** Sapaan menurut jam Asia/Jayapura (docs/RETEMA_B.md 5.1): pagi 00.00-10.59, siang 11.00-14.59, sore 15.00-17.59, malam 18.00-23.59. */
    private function sapaan(): string
    {
        $jam = (int) now()->format('G');

        return match (true) {
            $jam < 11 => 'pagi',
            $jam < 15 => 'siang',
            $jam < 18 => 'sore',
            default => 'malam',
        };
    }

    private function subjudul(int $menunggu): string
    {
        $kalimat = $menunggu > 0
            ? 'Ada '.$menunggu.' pesanan yang menunggu Anda.'
            : 'Tidak ada pesanan yang menunggu Anda.';

        return now()->translatedFormat('l, j F Y').'. '.$kalimat;
    }
}
