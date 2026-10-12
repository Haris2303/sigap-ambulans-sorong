<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dipakai agent/shot.mjs sebelum migrate: menolak berjalan bila DB aktif bukan database/shot.sqlite (docs/RETEMA_B.md, Banding visual).
Artisan::command('shot:pastikan-db', function () {
    if (! app()->environment('shot')) {
        $this->error('Perintah ini hanya untuk environment shot (APP_ENV=shot).');

        return 1;
    }

    $aktif = str_replace('\\', '/', (string) config('database.connections.sqlite.database'));
    $diharapkan = str_replace('\\', '/', database_path('shot.sqlite'));

    if ($aktif !== $diharapkan) {
        $this->error('DB aktif bukan database/shot.sqlite: '.$aktif);

        return 1;
    }

    $this->info('DB aktif: '.$aktif);

    return 0;
})->purpose('Pastikan DB aktif adalah database/shot.sqlite sebelum migrate di mode shot');
