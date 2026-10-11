<?php

return [

    // Jarak (detik) antar permintaan polling; dibaca lewat atribut data-* di view.
    'polling' => [
        'admin_detik' => 10,
        'sopir_detik' => 8,
        'warga_detik' => 10,
        // Sopir mengirim posisi GPS tiap sekian detik selama online.
        'sopir_lokasi_detik' => 15,
    ],

    // Data ketersediaan Faskes lebih tua dari ini ditandai "Data lama".
    'data_basi_jam' => 24,

    // Pusat peta awal: Kota Sorong, Papua Barat Daya.
    'peta' => [
        'pusat_lat' => -0.8762,
        'pusat_lng' => 131.2558,
        'zoom' => 13,
        // Satu-satunya layanan jaringan eksternal: ubin OpenStreetMap, pencarian Nominatim, rute OSRM demo.
        'ubin_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        'nominatim_url' => 'https://nominatim.openstreetmap.org',
        'osrm_url' => 'https://router.project-osrm.org/route/v1',
    ],

    'per_halaman' => 20,

    // Sandi akun demo. Kosong: "password" di local/testing, selain itu acak dan dicetak sekali.
    'seed_password' => env('SEED_PASSWORD'),

    // Menu rel per peran: nama route, label, ikon (daftar ikon di docs/KIT_UI.md bagian 6).
    'menu' => [
        'warga' => [
            ['route' => 'warga.beranda', 'cocok' => 'warga.beranda', 'label' => 'Beranda', 'ikon' => 'house'],
            ['route' => 'warga.pesanan.buat', 'cocok' => 'warga.pesanan.buat', 'label' => 'Pesan ambulans', 'ikon' => 'plus'],
            ['route' => 'warga.pesanan.index', 'cocok' => 'warga.pesanan.index', 'label' => 'Riwayat pesanan', 'ikon' => 'clipboard-list'],
            ['route' => 'faskes.index', 'cocok' => 'faskes.*', 'label' => 'Info Faskes', 'ikon' => 'hospital'],
        ],
        'admin' => [
            ['route' => 'admin.dashboard', 'cocok' => 'admin.dashboard', 'label' => 'Dasbor', 'ikon' => 'layout-dashboard'],
            ['route' => 'admin.pesanan.index', 'cocok' => 'admin.pesanan.*', 'label' => 'Pesanan', 'ikon' => 'clipboard-list', 'lencana' => 'pesanan_menunggu'],
            ['route' => 'admin.ketersediaan.index', 'cocok' => 'admin.ketersediaan.*', 'label' => 'Ketersediaan', 'ikon' => 'bed-double'],
            ['route' => 'admin.faskes.index', 'cocok' => 'admin.faskes.*', 'label' => 'Faskes', 'ikon' => 'building-2'],
            ['route' => 'admin.ambulans.index', 'cocok' => 'admin.ambulans.*', 'label' => 'Ambulans', 'ikon' => 'navigation'],
            ['route' => 'admin.sopir.index', 'cocok' => 'admin.sopir.*', 'label' => 'Sopir', 'ikon' => 'user'],
            ['route' => 'admin.petugas.index', 'cocok' => 'admin.petugas.*', 'label' => 'Petugas Faskes', 'ikon' => 'users'],
            ['route' => 'admin.laporan.index', 'cocok' => 'admin.laporan.*', 'label' => 'Laporan', 'ikon' => 'file-text'],
        ],
        'petugas_faskes' => [
            ['route' => 'petugas.dashboard', 'cocok' => 'petugas.dashboard', 'label' => 'Dasbor', 'ikon' => 'layout-dashboard'],
            ['route' => 'petugas.ketersediaan.edit', 'cocok' => 'petugas.ketersediaan.*', 'label' => 'Ketersediaan', 'ikon' => 'bed-double'],
            ['route' => 'faskes.index', 'cocok' => 'faskes.*', 'label' => 'Info Faskes', 'ikon' => 'hospital'],
        ],
        'sopir' => [
            ['route' => 'sopir.dashboard', 'cocok' => 'sopir.dashboard', 'label' => 'Penugasan', 'ikon' => 'navigation'],
        ],
    ],

];
