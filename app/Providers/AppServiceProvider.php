<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\MenuComposer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keduanya butuh $lencanaMenu: bilah samping/laci (lencana menu Pesanan) dan kepala halaman (titik lonceng).
        View::composer(['components.layouts.app', 'components.ui.judul-halaman'], MenuComposer::class);

        // Akun peran lain pada {sopir}/{petugas} menghasilkan 404.
        Route::bind('sopir', fn (string $nilai) => User::sopir()->findOrFail($nilai));
        Route::bind('petugas', fn (string $nilai) => User::petugas()->findOrFail($nilai));

        // Mode shot (docs/RETEMA_B.md, Banding visual): waktu dibekukan supaya sapaan, tanggal, dan waktu tunggu sama di setiap tangkapan layar.
        if (app()->environment('shot')) {
            Carbon::setTestNow(Carbon::parse('2026-10-11 21:26:00', config('app.timezone')));
        }
    }
}
