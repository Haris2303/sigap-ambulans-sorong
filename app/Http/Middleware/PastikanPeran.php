<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
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

        $diizinkan = array_map(fn (string $p) => Role::from($p), $peran);
        if (! $user->punyaPeran(...$diizinkan)) {
            abort(403, 'Anda tidak berhak mengakses halaman ini.');
        }

        return $next($request);
    }
}
