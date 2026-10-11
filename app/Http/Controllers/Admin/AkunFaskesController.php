<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AkunFaskesRequest;
use App\Models\Faskes;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Dasar pengelolaan akun Sopir dan Petugas Faskes oleh Dinkes (tanpa hapus; nonaktifkan lewat kolom aktif). */
abstract class AkunFaskesController extends Controller
{
    abstract protected function peran(): Role;

    /** Awalan nama route, mis. "sopir" untuk admin.sopir.index. */
    abstract protected function rute(): string;

    abstract protected function judul(): string;

    protected function daftar(Request $request): View
    {
        $faskesId = (int) $request->query('faskes_id');
        $cari = trim((string) $request->query('cari'));

        $daftar = User::with('faskes')
            ->where('role', $this->peran()->value)
            ->when($faskesId > 0, fn (Builder $q) => $q->where('faskes_id', $faskesId))
            ->when($cari !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->where('name', 'like', '%'.$cari.'%')->orWhere('email', 'like', '%'.$cari.'%')))
            ->orderBy('name')
            ->paginate(config('sigap.per_halaman'))
            ->withQueryString();

        return view('admin.akun.index', $this->dataTampil() + [
            'daftar' => $daftar,
            'faskesId' => $faskesId ?: null,
            'cari' => $cari,
            'adaFilter' => $faskesId > 0 || $cari !== '',
            'opsiFaskes' => $this->opsiFaskes(),
        ]);
    }

    protected function formBaru(): View
    {
        return view('admin.akun.form', $this->dataTampil() + [
            'akun' => new User(['aktif' => true]),
            'baru' => true,
            'opsiFaskes' => $this->opsiFaskes(),
        ]);
    }

    protected function formUbah(User $akun): View
    {
        return view('admin.akun.form', $this->dataTampil() + [
            'akun' => $akun,
            'baru' => false,
            'opsiFaskes' => $this->opsiFaskes(),
        ]);
    }

    protected function simpanBaru(AkunFaskesRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $akun = new User(collect($data)->except('password')->all());
        $akun->password = $data['password'];
        $akun->role = $this->peran();
        $akun->save();

        return redirect()->route($this->rute().'.index')->with('sukses', $this->judul().' '.$akun->name.' berhasil ditambahkan.');
    }

    protected function simpanUbah(AkunFaskesRequest $request, User $akun): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $akun->update($data);

        return redirect()->route($this->rute().'.index')->with('sukses', $this->judul().' '.$akun->name.' berhasil disimpan.');
    }

    protected function balikAktif(Request $request, User $akun): RedirectResponse
    {
        if ($akun->is($request->user())) {
            return redirect()->route($this->rute().'.index')->with('galat', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        if ($akun->aktif && $akun->sedangBertugas()) {
            return redirect()->route($this->rute().'.index')->with('galat', $akun->name.' masih memegang pesanan aktif dan belum dapat dinonaktifkan.');
        }

        DB::transaction(function () use ($akun) {
            $akun->aktif = ! $akun->aktif;
            if (! $akun->aktif) {
                $akun->sedang_online = false;
            }
            $akun->save();
        });

        return redirect()->route($this->rute().'.index')
            ->with('sukses', $this->judul().' '.$akun->name.($akun->aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
    }

    /** @return array<string, mixed> */
    private function dataTampil(): array
    {
        return [
            'rute' => $this->rute(),
            'judulAkun' => $this->judul(),
            'peran' => $this->peran(),
        ];
    }

    /** @return array<int, string> */
    private function opsiFaskes(): array
    {
        return Faskes::aktif()->orderBy('nama')->pluck('nama', 'id')->all();
    }
}
