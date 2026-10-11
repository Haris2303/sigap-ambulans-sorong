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

    // Tautan navigasi sopir ke lokasi jemput (App\Support\Navigasi). {lat}, {lng}, dan {alamat} diisi otomatis. `aplikasi` membuka aplikasi
    // peta bawaan perangkat (skema geo:, Android); `web` adalah cadangan di peramban dan hanya memakai OpenStreetMap, layanan yang sudah dipakai
    // aplikasi. Templat berakhiran _alamat dipakai bila pesanan tidak punya koordinat.
    'navigasi' => [
        'aplikasi' => 'geo:{lat},{lng}?q={lat},{lng}(Lokasi%20jemput)',
        'aplikasi_alamat' => 'geo:0,0?q={alamat}',
        'web' => 'https://www.openstreetmap.org/?mlat={lat}&mlon={lng}#map=17/{lat}/{lng}',
        'web_alamat' => 'https://www.openstreetmap.org/search?query={alamat}',
    ],

    // Waktu tunggu pesanan yang belum ditugaskan (Menunggu dan Terverifikasi), dalam menit sejak dibuat: mulai `peringatan_menit` lencana waktu
    // tunggu berwarna kuning, mulai `bahaya_menit` merah. Nilai awal hanya contoh; Dinkes menetapkan batas yang dipakai.
    'waktu_tunggu' => [
        'peringatan_menit' => 5,
        'bahaya_menit' => 10,
    ],

    'per_halaman' => 20,

    // Sandi akun demo. Kosong: "password" di local/testing, selain itu acak dan dicetak sekali.
    'seed_password' => env('SEED_PASSWORD'),

    // Menu laci per peran: nama route, label, ikon (daftar ikon di docs/KIT_UI.md bagian 6). `grup` (opsional) memberi label
    // grup; label tampil saat grup berganti. `lencana` menunjuk kunci angka hidup (RingkasanDinkes).
    'menu' => [
        'warga' => [
            ['route' => 'warga.beranda', 'cocok' => 'warga.beranda', 'label' => 'Beranda', 'ikon' => 'house'],
            ['route' => 'warga.pesanan.buat', 'cocok' => 'warga.pesanan.buat', 'label' => 'Pesan ambulans', 'ikon' => 'plus'],
            ['route' => 'warga.pesanan.index', 'cocok' => 'warga.pesanan.index', 'label' => 'Riwayat pesanan', 'ikon' => 'clipboard-list'],
            ['route' => 'faskes.index', 'cocok' => 'faskes.*', 'label' => 'Info Faskes', 'ikon' => 'hospital'],
        ],
        'admin' => [
            ['route' => 'admin.dashboard', 'cocok' => 'admin.dashboard', 'label' => 'Dasbor', 'ikon' => 'layout-dashboard', 'grup' => 'Operasional'],
            ['route' => 'admin.pesanan.index', 'cocok' => 'admin.pesanan.*', 'label' => 'Pesanan', 'ikon' => 'clipboard-list', 'lencana' => 'pesanan_menunggu', 'grup' => 'Operasional'],
            ['route' => 'admin.ketersediaan.index', 'cocok' => 'admin.ketersediaan.*', 'label' => 'Ketersediaan', 'ikon' => 'bed-double', 'grup' => 'Operasional'],
            ['route' => 'admin.laporan.index', 'cocok' => 'admin.laporan.*', 'label' => 'Laporan', 'ikon' => 'file-text', 'grup' => 'Operasional'],
            ['route' => 'admin.faskes.index', 'cocok' => 'admin.faskes.*', 'label' => 'Faskes', 'ikon' => 'building-2', 'grup' => 'Data master'],
            ['route' => 'admin.ambulans.index', 'cocok' => 'admin.ambulans.*', 'label' => 'Ambulans', 'ikon' => 'ambulance', 'grup' => 'Data master'],
            ['route' => 'admin.sopir.index', 'cocok' => 'admin.sopir.*', 'label' => 'Sopir', 'ikon' => 'user', 'grup' => 'Data master'],
            ['route' => 'admin.petugas.index', 'cocok' => 'admin.petugas.*', 'label' => 'Petugas Faskes', 'ikon' => 'users', 'grup' => 'Data master'],
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

    // Bilah atas (kartu melayang). `cari`: route daftar pesanan per peran yang menerima GET ?q=; peran yang tidak tercantum
    // tidak punya kolom cari. `lonceng`: jumlah pesanan menunggu verifikasi (bukan notifikasi umum), hanya Dinkes.
    'bilah_atas' => [
        'cari' => ['admin' => 'admin.pesanan.index'],
        'lonceng' => [
            'admin' => [
                'route' => 'admin.pesanan.index',
                'query' => ['tab' => 'perlu-tindakan'],
                'lencana' => 'pesanan_menunggu',
                'label' => 'Pesanan menunggu verifikasi',
            ],
        ],
    ],

];
