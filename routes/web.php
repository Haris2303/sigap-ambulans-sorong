<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AmbulansController as AdminAmbulansController;
use App\Http\Controllers\Admin\FaskesController as AdminFaskesController;
use App\Http\Controllers\Admin\KetersediaanController as AdminKetersediaanController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\PetugasController as AdminPetugasController;
use App\Http\Controllers\Admin\SopirController as AdminSopirController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Publik\BerandaController;
use App\Http\Controllers\Publik\FaskesController;
use App\Http\Controllers\Publik\KatalogUiController;
use App\Http\Controllers\Petugas\AmbulansController as PetugasAmbulansController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\KetersediaanController as PetugasKetersediaanController;
use App\Http\Controllers\Sopir\DashboardController as SopirDashboardController;
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

// Petugas Faskes
Route::middleware(['auth', 'role:petugas_faskes'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/', [PetugasDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/pesanan', [PetugasDashboardController::class, 'pesananIndex'])->name('pesanan.index');
    Route::get('/pesanan/{pesanan}', [PetugasDashboardController::class, 'pesananShow'])->name('pesanan.show');
    Route::get('/ketersediaan', [PetugasKetersediaanController::class, 'edit'])->name('ketersediaan.edit');
    Route::put('/ketersediaan', [PetugasKetersediaanController::class, 'update'])->name('ketersediaan.update');
    Route::put('/ambulans/{ambulans}/status', [PetugasAmbulansController::class, 'status'])->name('ambulans.status');
});

// Sopir
Route::middleware(['auth', 'role:sopir'])->prefix('sopir')->name('sopir.')->group(function () {
    Route::get('/', [SopirDashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/online', [SopirDashboardController::class, 'online'])->name('online');
    Route::post('/lokasi', [SopirDashboardController::class, 'lokasi'])->middleware('throttle:120,1')->name('lokasi');
    Route::get('/penugasan', [SopirDashboardController::class, 'penugasan'])->name('penugasan');
    Route::post('/pesanan/{pesanan}/terima', [SopirDashboardController::class, 'terima'])->name('pesanan.terima');
    Route::post('/pesanan/{pesanan}/tolak', [SopirDashboardController::class, 'tolak'])->name('pesanan.tolak');
    Route::post('/pesanan/{pesanan}/berangkat', [SopirDashboardController::class, 'berangkat'])->name('pesanan.berangkat');
    Route::post('/pesanan/{pesanan}/selesai', [SopirDashboardController::class, 'selesaikan'])->name('pesanan.selesai');
});

// Dinkes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/pesanan', [AdminPesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/ringkasan', [AdminPesananController::class, 'ringkasan'])->name('pesanan.ringkasan');
    Route::get('/pesanan/{pesanan}', [AdminPesananController::class, 'show'])->name('pesanan.show');
    Route::put('/pesanan/{pesanan}/verifikasi', [AdminPesananController::class, 'verifikasi'])->name('pesanan.verifikasi');
    Route::put('/pesanan/{pesanan}/tolak', [AdminPesananController::class, 'tolak'])->name('pesanan.tolak');
    Route::put('/pesanan/{pesanan}/tugaskan', [AdminPesananController::class, 'tugaskan'])->name('pesanan.tugaskan');
    Route::put('/pesanan/{pesanan}/lepas', [AdminPesananController::class, 'lepas'])->name('pesanan.lepas');
    Route::put('/pesanan/{pesanan}/batalkan', [AdminPesananController::class, 'batalkan'])->name('pesanan.batalkan');

    Route::resource('faskes', AdminFaskesController::class)->except(['show', 'destroy'])->parameters(['faskes' => 'faskes']);
    Route::put('/faskes/{faskes}/aktif', [AdminFaskesController::class, 'aktif'])->name('faskes.aktif');

    Route::resource('ambulans', AdminAmbulansController::class)->except(['show', 'destroy'])->parameters(['ambulans' => 'ambulans']);

    Route::resource('sopir', AdminSopirController::class)->except(['show', 'destroy'])->parameters(['sopir' => 'sopir']);
    Route::put('/sopir/{sopir}/aktif', [AdminSopirController::class, 'aktif'])->name('sopir.aktif');

    Route::resource('petugas', AdminPetugasController::class)->except(['show', 'destroy'])->parameters(['petugas' => 'petugas']);
    Route::put('/petugas/{petugas}/aktif', [AdminPetugasController::class, 'aktif'])->name('petugas.aktif');

    Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [AdminLaporanController::class, 'pdf'])->name('laporan.pdf');
    Route::get('/laporan/csv', [AdminLaporanController::class, 'csv'])->name('laporan.csv');

    Route::get('/ketersediaan', [AdminKetersediaanController::class, 'index'])->name('ketersediaan.index');
    Route::put('/ketersediaan/{faskes}', [AdminKetersediaanController::class, 'update'])->name('ketersediaan.update');
});

if (app()->environment('local', 'testing', 'shot')) {
    Route::get('/_ui', KatalogUiController::class)->name('ui.katalog');
}
