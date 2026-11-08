<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisFaskes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaskesRequest;
use App\Models\Faskes;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaskesController extends Controller
{
    public function index(Request $request): View
    {
        $jenis = JenisFaskes::tryFrom((string) $request->query('jenis'));
        $cari = trim((string) $request->query('cari'));

        $daftar = Faskes::query()
            ->withCount('ambulans')
            ->when($jenis, fn (Builder $q) => $q->where('jenis', $jenis->value))
            ->when($cari !== '', fn (Builder $q) => $q->where('nama', 'like', '%'.$cari.'%'))
            ->orderBy('nama')
            ->paginate(config('sigap.per_halaman'))
            ->withQueryString();

        return view('admin.faskes.index', [
            'daftar' => $daftar,
            'jenis' => $jenis,
            'cari' => $cari,
            'adaFilter' => $jenis !== null || $cari !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.faskes.form', ['faskes' => new Faskes(['aktif' => true]), 'baru' => true]);
    }

    public function store(FaskesRequest $request): RedirectResponse
    {
        $faskes = Faskes::create($request->validated());

        return redirect()->route('admin.faskes.index')->with('sukses', 'Faskes '.$faskes->nama.' berhasil ditambahkan.');
    }

    public function edit(Faskes $faskes): View
    {
        return view('admin.faskes.form', ['faskes' => $faskes, 'baru' => false]);
    }

    public function update(FaskesRequest $request, Faskes $faskes): RedirectResponse
    {
        $faskes->update($request->validated());

        return redirect()->route('admin.faskes.index')->with('sukses', 'Faskes '.$faskes->nama.' berhasil disimpan.');
    }

    /** Satu-satunya cara "menghapus": membalik kolom aktif. */
    public function aktif(Faskes $faskes): RedirectResponse
    {
        $faskes->update(['aktif' => ! $faskes->aktif]);

        return redirect()->route('admin.faskes.index')
            ->with('sukses', 'Faskes '.$faskes->nama.($faskes->aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
    }
}
