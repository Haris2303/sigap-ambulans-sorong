<x-layouts.publik judul="Beranda">
    {{-- Pengecualian sempit dari DESIGN.md (keputusan pemilik proyek, lihat docs/laporan/ASUMSI_DAN_KEPUTUSAN.md):
         animasi mengambang untuk ilustrasi hero. Hanya pada halaman ini. --}}
    <style>
        @keyframes sigap-mengambang { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes sigap-mengambang-lambat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        .sigap-mengambang { animation: sigap-mengambang 5s ease-in-out infinite; }
        .sigap-mengambang-lambat { animation: sigap-mengambang-lambat 7s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) {
            .sigap-mengambang, .sigap-mengambang-lambat { animation: none; }
        }
    </style>

    <div class="relative overflow-hidden rounded-2xl bg-secondary p-6 text-on-secondary shadow-card lg:p-12">
        <span aria-hidden="true" class="absolute -top-16 -right-16 size-56 rounded-full bg-primary/25"></span>
        <span aria-hidden="true" class="absolute -bottom-20 right-24 size-40 rounded-full bg-primary/15"></span>
        <span aria-hidden="true" class="absolute left-20 top-6 hidden size-24 rounded-full bg-on-secondary/8 lg:block"></span>

        <div class="relative grid items-center gap-10 lg:grid-cols-[1.1fr_1fr]">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-on-secondary/15 px-3 py-1 text-label-m">
                    <x-ui.ikon nama="circle-check" :ukuran="16" />
                    Layanan resmi Dinas Kesehatan Kota Sorong
                </span>
                <h1 class="mt-4 text-stat-hero max-lg:text-page-title-s">Pesan ambulans Dinas Kesehatan Kota Sorong.</h1>
                <p class="mt-4 max-w-lg text-body-l text-primary-container">SIGAP Ambulans menerima pesanan ambulans dari Masyarakat untuk keadaan darurat, rujukan, dan persalinan. Dinas Kesehatan memverifikasi setiap pesanan, lalu menugaskan ambulans dan sopir dari Fasilitas Kesehatan terdekat.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <x-ui.tombol nada="on-secondary" :href="$aksiUtama['url']">{{ $aksiUtama['label'] }}</x-ui.tombol>
                    <x-ui.tombol nada="on-secondary" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
                </div>
            </div>

            <div aria-hidden="true" class="hidden justify-self-center lg:block">
                <svg viewBox="0 0 360 360" class="w-full max-w-90">
                    <circle cx="180" cy="180" r="128" fill="var(--color-on-secondary)" fill-opacity="0.12" />
                    <circle cx="180" cy="180" r="104" fill="var(--color-on-secondary)" />
                    <g transform="translate(120,120) scale(5)" fill="none" stroke="var(--color-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 10H6" />
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.28a1 1 0 0 0-.684-.948l-1.923-.641a1 1 0 0 1-.578-.502l-1.539-3.076A1 1 0 0 0 16.382 8H14" />
                        <path d="M8 8v4" />
                        <path d="M9 18h6" />
                        <circle cx="17" cy="18" r="2" />
                        <circle cx="7" cy="18" r="2" />
                    </g>

                    <path d="M110 255 Q 150 230 170 212" fill="none" stroke="var(--color-on-secondary)" stroke-opacity="0.35" stroke-width="3" stroke-linecap="round" stroke-dasharray="1 10" />
                    <path d="M130 332 h18 l8 -18 l10 36 l8 -18 h22" fill="none" stroke="var(--color-on-secondary)" stroke-opacity="0.45" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                    <g class="sigap-mengambang">
                        <circle cx="280" cy="100" r="34" fill="var(--color-on-secondary)" fill-opacity="0.15" />
                        <circle cx="280" cy="100" r="28" fill="var(--color-on-secondary)" />
                        <g transform="translate(263,83) scale(1.4)" fill="none" stroke="var(--color-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m16 9-5.5 5.5L8 12" />
                        </g>
                    </g>

                    <g class="sigap-mengambang-lambat">
                        <circle cx="85" cy="270" r="32" fill="var(--color-on-secondary)" fill-opacity="0.15" />
                        <circle cx="85" cy="270" r="26" fill="var(--color-on-secondary)" />
                        <g transform="translate(69,254) scale(1.3)" fill="none" stroke="var(--color-tertiary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                            <circle cx="12" cy="10" r="3" />
                        </g>
                    </g>

                    <circle cx="55" cy="130" r="6" fill="var(--color-chart-amber)" class="sigap-mengambang" />
                    <circle cx="305" cy="225" r="8" fill="var(--color-chart-mint)" class="sigap-mengambang-lambat" />
                    <circle cx="245" cy="300" r="5" fill="var(--color-chart-coral)" class="sigap-mengambang" />
                </svg>
            </div>
        </div>
    </div>

    <div class="relative z-10 -mt-6 grid gap-4 px-1 sm:grid-cols-3 lg:-mt-8 lg:px-0">
        <x-ui.stat tone="primary" icon="ambulance" :value="$statistik['ambulans']" label="Ambulans siap melayani" hint="dari seluruh Faskes" />
        <x-ui.stat tone="tertiary" icon="building-2" :value="$statistik['faskes']" label="Fasilitas Kesehatan terhubung" hint="Rumah sakit, Puskesmas, klinik" />
        <x-ui.stat tone="success" icon="list-checks" :value="$statistik['layanan']" label="Jenis layanan tersedia" hint="Darurat, rujukan, persalinan" />
    </div>

    <x-ui.kontak-darurat class="mt-8 max-w-2xl">Keadaan darurat dan aplikasi ini tidak bisa dipakai?</x-ui.kontak-darurat>

    <div class="relative mt-8 overflow-hidden rounded-2xl bg-surface-container-low p-6 lg:p-10">
        <span aria-hidden="true" class="absolute -top-10 -right-10 hidden size-48 rounded-full bg-primary/8 lg:block"></span>

        <x-ui.bagian judul="Mengapa SIGAP Ambulans" deskripsi="Satu sistem yang menghubungkan Masyarakat, Dinas Kesehatan, dan Fasilitas Kesehatan di Kota Sorong supaya penanganan darurat lebih cepat dan tertata." :jarak="false" class="relative">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.panel class="transition-transform hover:-translate-y-1">
                    <x-ui.icon-chip tone="success" icon="circle-check" size="lg" />
                    <p class="mt-3.5 text-title-m">Diverifikasi Dinkes</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Setiap pesanan diperiksa Dinas Kesehatan lebih dulu, baru ambulans dan sopir ditugaskan.</p>
                </x-ui.panel>
                <x-ui.panel class="transition-transform hover:-translate-y-1">
                    <x-ui.icon-chip tone="primary" icon="ambulance" size="lg" />
                    <p class="mt-3.5 text-title-m">Ambulans resmi dari Faskes terdekat</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Dinkes menugaskan ambulans dan sopir dari Fasilitas Kesehatan sesuai jenis layanan, bukan pilihan bebas pemohon.</p>
                </x-ui.panel>
                <x-ui.panel class="transition-transform hover:-translate-y-1">
                    <x-ui.icon-chip tone="tertiary" icon="map-pin" size="lg" />
                    <p class="mt-3.5 text-title-m">Pantau perjalanan</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Status pesanan dan lokasi sopir dapat dipantau sejak ambulans ditugaskan sampai tiba.</p>
                </x-ui.panel>
                <x-ui.panel class="transition-transform hover:-translate-y-1">
                    <x-ui.icon-chip tone="neutral" icon="list-checks" size="lg" />
                    <p class="mt-3.5 text-title-m">Tercatat dan transparan</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Setiap perubahan status tersimpan dalam linimasa pesanan, dari diajukan sampai selesai.</p>
                </x-ui.panel>
            </div>
        </x-ui.bagian>

        <x-ui.bagian judul="Layanan yang kami sediakan" deskripsi="Ajukan permintaan sesuai kebutuhan; Dinkes memverifikasi lalu menugaskan ambulans dan sopir yang sesuai." class="relative">
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (\App\Enums\JenisLayanan::cases() as $jenis)
                    <x-ui.panel class="transition-transform hover:-translate-y-1">
                        <x-ui.icon-chip tone="primary" :icon="$jenis->ikon()" size="lg" />
                        <p class="mt-3.5 text-title-m">{{ $jenis->label() }}</p>
                        <p class="mt-1 text-body-m text-on-surface-variant">{{ $jenis->deskripsi() }}</p>
                        @if ($jenis->wajibFaskesTujuan())
                            <x-ui.lencana nada="netral" :dot="false" size="sm" class="mt-3">Faskes tujuan wajib</x-ui.lencana>
                        @endif
                    </x-ui.panel>
                @endforeach
            </div>
        </x-ui.bagian>
    </div>

    <x-ui.bagian judul="Cara kerja">
        <x-ui.panel class="max-w-2xl">
            <ol class="list-decimal space-y-3 pl-6 text-body-l marker:text-primary">
                <li>Masyarakat mengisi pesanan dan lokasi jemput.</li>
                <li>Dinkes memverifikasi pesanan.</li>
                <li>Dinkes menugaskan ambulans dan sopir.</li>
                <li>Masyarakat memantau perjalanan ambulans sampai tiba.</li>
            </ol>
        </x-ui.panel>
    </x-ui.bagian>

    <div class="relative mt-8 overflow-hidden rounded-2xl bg-secondary-container p-6 lg:p-8">
        <span aria-hidden="true" class="absolute -bottom-12 -left-12 hidden size-40 rounded-full bg-primary/8 lg:block"></span>
        <div class="relative flex flex-col items-start justify-between gap-4 text-on-secondary-container lg:flex-row lg:items-center">
            <div class="min-w-0">
                <p class="text-title-l">Butuh ambulans sekarang?</p>
                <p class="mt-1 text-body-m">Ajukan pesanan dan Dinkes akan segera memverifikasi serta menugaskan ambulans terdekat.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.tombol nada="utama" :href="$aksiUtama['url']">{{ $aksiUtama['label'] }}</x-ui.tombol>
                <x-ui.tombol nada="sekunder" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
            </div>
        </div>
    </div>
</x-layouts.publik>
