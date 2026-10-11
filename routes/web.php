<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Publik\BerandaController;
use App\Http\Controllers\Publik\FaskesController;
use App\Http\Controllers\Publik\KatalogUiController;
use Illuminate\Support\Facades\Route;

Route::get('/', BerandaController::class)->name('beranda');
Route::get('/faskes', [FaskesController::class, 'index'])->name('faskes.index');
Route::get('/faskes/data', [FaskesController::class, 'data'])->name('faskes.data');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'tampil'])->name('login');
    Route::post('/login', [LoginController::class, 'proses'])->name('login.proses');
    Route::get('/register', [RegisterController::class, 'tampil'])->name('register');
    Route::post('/register', [RegisterController::class, 'proses'])->name('register.proses');
});

Route::post('/logout', [LoginController::class, 'keluar'])->middleware('auth')->name('logout');

if (app()->environment('local', 'testing')) {
    Route::get('/_ui', KatalogUiController::class)->name('ui.katalog');
}
