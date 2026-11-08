<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Requests\Admin\AkunFaskesRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PetugasController extends AkunFaskesController
{
    protected function peran(): Role
    {
        return Role::PetugasFaskes;
    }

    protected function rute(): string
    {
        return 'admin.petugas';
    }

    protected function judul(): string
    {
        return 'Petugas Faskes';
    }

    public function index(Request $request): View
    {
        return $this->daftar($request);
    }

    public function create(): View
    {
        return $this->formBaru();
    }

    public function store(AkunFaskesRequest $request): RedirectResponse
    {
        return $this->simpanBaru($request);
    }

    public function edit(User $petugas): View
    {
        return $this->formUbah($petugas);
    }

    public function update(AkunFaskesRequest $request, User $petugas): RedirectResponse
    {
        return $this->simpanUbah($request, $petugas);
    }

    public function aktif(Request $request, User $petugas): RedirectResponse
    {
        return $this->balikAktif($request, $petugas);
    }
}
