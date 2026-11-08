<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Requests\Admin\AkunFaskesRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SopirController extends AkunFaskesController
{
    protected function peran(): Role
    {
        return Role::Sopir;
    }

    protected function rute(): string
    {
        return 'admin.sopir';
    }

    protected function judul(): string
    {
        return 'Sopir';
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

    public function edit(User $sopir): View
    {
        return $this->formUbah($sopir);
    }

    public function update(AkunFaskesRequest $request, User $sopir): RedirectResponse
    {
        return $this->simpanUbah($request, $sopir);
    }

    public function aktif(Request $request, User $sopir): RedirectResponse
    {
        return $this->balikAktif($request, $sopir);
    }
}
