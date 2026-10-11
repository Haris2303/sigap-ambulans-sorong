<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Publik\BerandaController;
use App\Http\Controllers\Publik\FaskesController;
use App\Http\Controllers\Publik\KatalogUiController;
use App\Http\Controllers\Warga\BerandaController as WargaBerandaController;
use App\Http\Controllers\Warga\PesananController as WargaPesananController;
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

// Masyarakat
Route::middleware(['auth', 'role:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/', WargaBerandaController::class)->name('beranda');
    Route::get('/pesanan', [WargaPesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/buat', [WargaPesananController::class, 'buat'])->name('pesanan.buat');
    Route::post('/pesanan/tinjau', [WargaPesananController::class, 'tinjau'])->name('pesanan.tinjau');
    Route::get('/pesanan/konfirmasi', [WargaPesananController::class, 'konfirmasi'])->name('pesanan.konfirmasi');
    Route::post('/pesanan', [WargaPesananController::class, 'simpan'])->name('pesanan.simpan');
    Route::get('/pesanan/{pesanan}/status', [WargaPesananController::class, 'status'])->name('pesanan.status');
    Route::get('/pesanan/{pesanan}', [WargaPesananController::class, 'show'])->name('pesanan.show');
    Route::put('/pesanan/{pesanan}/batal', [WargaPesananController::class, 'batalkan'])->name('pesanan.batalkan');
});

if (app()->environment('local', 'testing')) {
    Route::get('/_ui', KatalogUiController::class)->name('ui.katalog');
}
