<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function tampil(): View
    {
        return view('auth.login');
    }

    public function proses(LoginRequest $request): RedirectResponse
    {
        $request->autentikasi();
        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->role->ruteBeranda()));
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('sukses', 'Anda sudah keluar.');
    }
}
