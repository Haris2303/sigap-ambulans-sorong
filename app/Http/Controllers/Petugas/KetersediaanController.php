<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\KetersediaanFaskesRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KetersediaanController extends Controller
{
    public function edit(Request $request): View
    {
        $faskes = $request->user()->faskes;
        abort_if($faskes === null, 403);

        return view('petugas.ketersediaan.edit', ['faskes' => $faskes]);
    }

    /** Petugas hanya dapat memperbarui Faskes miliknya sendiri; Faskes tidak datang dari isian. */
    public function update(KetersediaanFaskesRequest $request): RedirectResponse
    {
        $faskes = $request->user()->faskes;
        abort_if($faskes === null, 403);

        $faskes->perbaruiKetersediaan($request->validated());

        return redirect()->route('petugas.ketersediaan.edit')->with('sukses', 'Ketersediaan berhasil disimpan.');
    }
}
