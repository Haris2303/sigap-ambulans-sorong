<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\MenuComposer;
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
        View::composer('components.layouts.app', MenuComposer::class);

        // Akun peran lain pada {sopir}/{petugas} menghasilkan 404.
        Route::bind('sopir', fn (string $nilai) => User::sopir()->findOrFail($nilai));
        Route::bind('petugas', fn (string $nilai) => User::petugas()->findOrFail($nilai));
    }
}
