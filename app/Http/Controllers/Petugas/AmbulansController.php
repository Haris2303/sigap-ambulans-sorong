<?php

namespace App\Http\Controllers\Petugas;

use App\Enums\StatusAmbulans;
use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\StatusAmbulansRequest;
use App\Models\Ambulans;
use Illuminate\Http\RedirectResponse;

class AmbulansController extends Controller
{
    /** Petugas hanya menukar tersedia dan servis pada ambulans Faskes-nya; bertugas diatur workflow. */
    public function status(StatusAmbulansRequest $request, Ambulans $ambulans): RedirectResponse
    {
        abort_unless($request->user()->faskes_id !== null && $ambulans->faskes_id === $request->user()->faskes_id, 403);

        if ($ambulans->status === StatusAmbulans::Bertugas) {
            return redirect()->route('petugas.dashboard')->with('galat', 'Ambulans '.$ambulans->plat_nomor.' sedang bertugas dan statusnya belum dapat diubah.');
        }

        $baru = StatusAmbulans::from($request->validated('status'));
        $ambulans->update(['status' => $baru]);

        return redirect()->route('petugas.dashboard')->with('sukses', 'Status ambulans '.$ambulans->plat_nomor.' diubah menjadi '.$baru->label().'.');
    }
}
