<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Alias `role`: contoh `role:admin` atau `role:petugas_faskes,sopir`. Dipasang setelah `auth`. */
class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();
        if ($user === null) {
            return redirect()->route('login');
        }

        // Akun yang dinonaktifkan Dinkes saat sedang masuk langsung dikeluarkan.
        if (! $user->aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('galat', trans('auth.nonaktif'));
        }

        $diizinkan = array_map(fn (string $p) => Role::from($p), $peran);
        if (! $user->punyaPeran(...$diizinkan)) {
            abort(403);
        }

        return $next($request);
    }
}
