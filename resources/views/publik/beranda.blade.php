<x-layouts.publik judul="Beranda">
    <div class="relative overflow-hidden rounded-2xl bg-secondary p-6 text-on-secondary shadow-card lg:p-10">
        <span aria-hidden="true" class="absolute -top-12.5 -right-12.5 size-42.5 rounded-full bg-primary/25"></span>
        <span aria-hidden="true" class="absolute -bottom-17.5 right-7.5 size-35 rounded-full bg-primary/15"></span>
        <div class="relative max-w-2xl">
            <h1 class="text-page-title max-lg:text-page-title-s">Pesan ambulans Dinas Kesehatan Kota Sorong.</h1>
            <p class="mt-3 text-body-l text-primary-container">SIGAP Ambulans menerima pesanan ambulans dari Masyarakat untuk keadaan darurat, rujukan, dan persalinan. Dinas Kesehatan memverifikasi setiap pesanan, lalu menugaskan ambulans dan sopir dari Fasilitas Kesehatan terdekat.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <x-ui.tombol nada="on-secondary" :href="$aksiUtama['url']">{{ $aksiUtama['label'] }}</x-ui.tombol>
                <x-ui.tombol nada="on-secondary" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
            </div>
        </div>
    </div>

    <div class="relative z-10 -mt-6 grid gap-4 px-1 sm:grid-cols-3 lg:-mt-8 lg:px-0">
        <x-ui.stat tone="primary" icon="ambulance" :value="$statistik['ambulans']" label="Ambulans siap melayani" hint="dari seluruh Faskes" />
        <x-ui.stat tone="tertiary" icon="building-2" :value="$statistik['faskes']" label="Fasilitas Kesehatan terhubung" hint="Rumah sakit, Puskesmas, klinik" />
        <x-ui.stat tone="success" icon="list-checks" :value="$statistik['layanan']" label="Jenis layanan tersedia" hint="Darurat, rujukan, persalinan" />
    </div>

    <x-ui.kontak-darurat class="mt-8 max-w-2xl">Keadaan darurat dan aplikasi ini tidak bisa dipakai?</x-ui.kontak-darurat>

    <div class="mt-8 rounded-2xl bg-surface-container-low p-6 lg:p-10">
        <x-ui.bagian judul="Mengapa SIGAP Ambulans" deskripsi="Satu sistem yang menghubungkan Masyarakat, Dinas Kesehatan, dan Fasilitas Kesehatan di Kota Sorong supaya penanganan darurat lebih cepat dan tertata." :jarak="false">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.panel>
                    <x-ui.icon-chip tone="success" icon="circle-check" size="lg" />
                    <p class="mt-3.5 text-title-m">Diverifikasi Dinkes</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Setiap pesanan diperiksa Dinas Kesehatan lebih dulu, baru ambulans dan sopir ditugaskan.</p>
                </x-ui.panel>
                <x-ui.panel>
                    <x-ui.icon-chip tone="primary" icon="ambulance" size="lg" />
                    <p class="mt-3.5 text-title-m">Ambulans resmi dari Faskes terdekat</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Dinkes menugaskan ambulans dan sopir dari Fasilitas Kesehatan sesuai jenis layanan, bukan pilihan bebas pemohon.</p>
                </x-ui.panel>
                <x-ui.panel>
                    <x-ui.icon-chip tone="tertiary" icon="map-pin" size="lg" />
                    <p class="mt-3.5 text-title-m">Pantau perjalanan</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Status pesanan dan lokasi sopir dapat dipantau sejak ambulans ditugaskan sampai tiba.</p>
                </x-ui.panel>
                <x-ui.panel>
                    <x-ui.icon-chip tone="neutral" icon="list-checks" size="lg" />
                    <p class="mt-3.5 text-title-m">Tercatat dan transparan</p>
                    <p class="mt-1 text-body-m text-on-surface-variant">Setiap perubahan status tersimpan dalam linimasa pesanan, dari diajukan sampai selesai.</p>
                </x-ui.panel>
            </div>
        </x-ui.bagian>

        <x-ui.bagian judul="Layanan yang kami sediakan" deskripsi="Ajukan permintaan sesuai kebutuhan; Dinkes memverifikasi lalu menugaskan ambulans dan sopir yang sesuai.">
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (\App\Enums\JenisLayanan::cases() as $jenis)
                    <x-ui.panel>
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
            <ol class="list-decimal space-y-2 pl-5 text-body-l">
                <li>Masyarakat mengisi pesanan dan lokasi jemput.</li>
                <li>Dinkes memverifikasi pesanan.</li>
                <li>Dinkes menugaskan ambulans dan sopir.</li>
                <li>Masyarakat memantau perjalanan ambulans sampai tiba.</li>
            </ol>
        </x-ui.panel>
    </x-ui.bagian>

    <div class="mt-8 rounded-2xl bg-secondary-container p-6 text-on-secondary-container lg:p-8">
        <div class="flex flex-col items-start justify-between gap-4 lg:flex-row lg:items-center">
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
