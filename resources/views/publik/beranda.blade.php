<x-layouts.publik judul="Beranda">
    <div class="max-w-prose">
        <div class="rounded-2xl bg-secondary p-6 text-on-secondary shadow-card lg:p-8">
            <h1 class="text-page-title max-lg:text-page-title-s">Pesan ambulans Dinas Kesehatan Kota Sorong.</h1>
            <p class="mt-3 text-body-l text-primary-container">SIGAP Ambulans menerima pesanan ambulans dari Masyarakat untuk keadaan darurat, rujukan, dan persalinan. Dinas Kesehatan memverifikasi setiap pesanan, lalu menugaskan ambulans dan sopir dari Fasilitas Kesehatan terdekat.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <x-ui.tombol nada="on-secondary" :href="$aksiUtama['url']">{{ $aksiUtama['label'] }}</x-ui.tombol>
                <x-ui.tombol nada="on-secondary" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
            </div>
        </div>
        <x-ui.kontak-darurat class="mt-6">Keadaan darurat dan aplikasi ini tidak bisa dipakai?</x-ui.kontak-darurat>

        <x-ui.bagian judul="Mengapa SIGAP Ambulans" deskripsi="Satu sistem yang menghubungkan Masyarakat, Dinas Kesehatan, dan Fasilitas Kesehatan di Kota Sorong supaya penanganan darurat lebih cepat dan tertata.">
            <x-ui.panel flush>
                <div class="divide-y divide-outline-variant">
                    <div class="flex items-start gap-3 p-5">
                        <x-ui.icon-chip tone="success" icon="circle-check" />
                        <div class="min-w-0">
                            <p class="text-title-m">Diverifikasi Dinkes</p>
                            <p class="mt-1 text-body-m text-on-surface-variant">Setiap pesanan diperiksa Dinas Kesehatan lebih dulu, baru ambulans dan sopir ditugaskan.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-5">
                        <x-ui.icon-chip tone="primary" icon="ambulance" />
                        <div class="min-w-0">
                            <p class="text-title-m">Ambulans resmi dari Faskes terdekat</p>
                            <p class="mt-1 text-body-m text-on-surface-variant">Dinkes menugaskan ambulans dan sopir dari Fasilitas Kesehatan sesuai jenis layanan, bukan pilihan bebas pemohon.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-5">
                        <x-ui.icon-chip tone="tertiary" icon="map-pin" />
                        <div class="min-w-0">
                            <p class="text-title-m">Pantau perjalanan</p>
                            <p class="mt-1 text-body-m text-on-surface-variant">Status pesanan dan lokasi sopir dapat dipantau sejak ambulans ditugaskan sampai tiba.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-5">
                        <x-ui.icon-chip tone="neutral" icon="list-checks" />
                        <div class="min-w-0">
                            <p class="text-title-m">Tercatat dan transparan</p>
                            <p class="mt-1 text-body-m text-on-surface-variant">Setiap perubahan status tersimpan dalam linimasa pesanan, dari diajukan sampai selesai.</p>
                        </div>
                    </div>
                </div>
            </x-ui.panel>
        </x-ui.bagian>

        <x-ui.bagian judul="Layanan yang kami sediakan" deskripsi="Ajukan permintaan sesuai kebutuhan; Dinkes memverifikasi lalu menugaskan ambulans dan sopir yang sesuai.">
            <x-ui.panel flush>
                <div class="divide-y divide-outline-variant">
                    @foreach (\App\Enums\JenisLayanan::cases() as $jenis)
                        <div class="flex items-start gap-3 p-5">
                            <x-ui.icon-chip tone="primary" :icon="$jenis->ikon()" />
                            <div class="min-w-0">
                                <p class="text-title-m">{{ $jenis->label() }}</p>
                                <p class="mt-1 text-body-m text-on-surface-variant">{{ $jenis->deskripsi() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.panel>
        </x-ui.bagian>

        <x-ui.bagian judul="Cara kerja">
            <x-ui.panel>
                <ol class="list-decimal space-y-2 pl-5 text-body-l">
                    <li>Masyarakat mengisi pesanan dan lokasi jemput.</li>
                    <li>Dinkes memverifikasi pesanan.</li>
                    <li>Dinkes menugaskan ambulans dan sopir.</li>
                    <li>Masyarakat memantau perjalanan ambulans sampai tiba.</li>
                </ol>
            </x-ui.panel>
        </x-ui.bagian>
    </div>
</x-layouts.publik>
