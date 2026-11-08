<?php

use App\Http\Middleware\PastikanPeran;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Pengguna yang sudah masuk dialihkan ke beranda perannya dari halaman tamu (login, register).
        $middleware->redirectUsersTo(fn () => route(auth()->user()->role->ruteBeranda()));

        $middleware->alias([
            'role' => PastikanPeran::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Konvensi JSON: { ok: false, pesan } dengan kode HTTP yang benar (RANCANGAN bagian 6).
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            [$status, $pesan] = match (true) {
                $e instanceof ValidationException => [422, $e->validator->errors()->first()],
                $e instanceof AuthenticationException => [401, 'Silakan masuk terlebih dahulu.'],
                $e instanceof AuthorizationException => [403, 'Anda tidak berhak mengakses data ini.'],
                $e instanceof ModelNotFoundException => [404, 'Data tidak ditemukan.'],
                $e instanceof HttpExceptionInterface => match ($e->getStatusCode()) {
                    403 => [403, 'Anda tidak berhak mengakses data ini.'],
                    404 => [404, 'Data tidak ditemukan.'],
                    419 => [419, 'Sesi berakhir. Muat ulang halaman.'],
                    default => [$e->getStatusCode(), 'Permintaan tidak dapat diproses.'],
                },
                default => [null, null],
            };

            if ($status === null) {
                return null;
            }

            return response()->json(['ok' => false, 'pesan' => $pesan], $status);
        });
    })->create();
