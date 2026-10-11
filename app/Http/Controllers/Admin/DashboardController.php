<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TabPesanan;
use App\Http\Controllers\Controller;
use App\Services\RingkasanDasbor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RingkasanDasbor $dasbor): View
    {
        $tab = TabPesanan::tryFrom((string) $request->query('tab')) ?? TabPesanan::Semua;

        return view('admin.dashboard', $dasbor->ambil($tab));
    }
}
