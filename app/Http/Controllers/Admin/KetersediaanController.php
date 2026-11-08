<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KetersediaanFaskesRequest;
use App\Models\Faskes;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class KetersediaanController extends Controller
{
    public function index(): View
    {
        return view('admin.ketersediaan.index', [
            'daftar' => Faskes::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function update(KetersediaanFaskesRequest $request, Faskes $faskes): RedirectResponse
    {
        $faskes->perbaruiKetersediaan($request->validated());

        return redirect()->route('admin.ketersediaan.index')
            ->with('sukses', 'Ketersediaan '.$faskes->nama.' berhasil disimpan.');
    }
}
