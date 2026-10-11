<?php

use App\Http\Controllers\Publik\KatalogUiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('beranda');

if (app()->environment('local', 'testing')) {
    Route::get('/_ui', KatalogUiController::class)->name('ui.katalog');
}
