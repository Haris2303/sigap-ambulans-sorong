<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function tampil(): View
    {
        return view('auth.register');
    }

    /** Pendaftaran mandiri hanya untuk Masyarakat; peran lain dibuat Dinkes. */
    public function proses(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'no_telepon' => $request->validated('no_telepon'),
            'password' => $request->validated('password'),
            'role' => Role::Warga,
            'aktif' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route(Role::Warga->ruteBeranda())->with('sukses', 'Akun berhasil dibuat.');
    }
}
