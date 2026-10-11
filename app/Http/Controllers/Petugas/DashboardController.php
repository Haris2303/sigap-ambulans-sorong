<?php

namespace App\Http\Controllers\Petugas;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Http\Controllers\Controller;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $faskes = $this->faskesPetugas($request);

        $aktif = Pesanan::with('ambulans')
            ->where(fn (Builder $q) => $q->where('faskes_tujuan_id', $faskes->id)
                ->orWhereHas('ambulans', fn (Builder $a) => $a->where('faskes_id', $faskes->id)))
            ->aktif()
            ->prioritas()
            ->get();

        return view('petugas.dashboard', [
            'faskes' => $faskes->loadCount(['ambulans as ambulans_tersedia_count' => fn (Builder $q) => $q->where('status', StatusAmbulans::Tersedia->value)]),
            'pesananAktif' => $aktif,
            'ambulans' => $faskes->ambulans()->with('sopirTetap')->orderBy('plat_nomor')->get(),
            'sopir' => User::where('role', Role::Sopir->value)->where('faskes_id', $faskes->id)->orderBy('name')->get(),
        ]);
    }

    public function pesananShow(Request $request, Pesanan $pesanan): View
    {
        $this->faskesPetugas($request);
        Gate::authorize('view', $pesanan);
        $pesanan->load(['log.pelaku', 'ambulans.faskes', 'sopir', 'faskesTujuan']);

        return view('petugas.pesanan.show', ['pesanan' => $pesanan]);
    }

    private function faskesPetugas(Request $request): Faskes
    {
        $faskes = $request->user()->faskes;
        abort_if($faskes === null, 403);

        return $faskes;
    }
}
