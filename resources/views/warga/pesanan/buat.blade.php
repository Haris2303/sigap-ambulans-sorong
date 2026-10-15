<x-layouts.app judul="Pesan ambulans" :peta="true">
    <x-ui.judul-halaman judul="Pesan ambulans" deskripsi="Isi data pesanan. Anda dapat memeriksanya kembali sebelum dikirim." />
    <x-ui.kontak-darurat varian="tautan" class="mb-6 max-w-3xl">Formulir tidak bisa dipakai?</x-ui.kontak-darurat>

    <x-ui.panel class="max-w-5xl">
        <form method="POST" action="{{ route('warga.pesanan.tinjau') }}" id="form-buat-pesanan">
            @csrf
            <div class="divide-y divide-outline-variant">
                {{-- Padding bagian dipasang di <div> pembungkus, bukan di <fieldset>: peramban menyisipkan
                     padding-block-start fieldset SETELAH legend (bukan sebelumnya) bila fieldset punya legend,
                     sehingga jarak judul ke isi jadi berlipat. <div> biasa tidak kena aturan itu.
                     Di lg ke atas, Jenis layanan dan Tingkat darurat berdampingan: pembagi mobile pindah ke
                     pembungkus dalam (lg:divide-y-0), jarak antar bagian dipindah ke pembungkus luar (lg:pb-6). --}}
                <div class="divide-y divide-outline-variant lg:grid lg:grid-cols-2 lg:gap-x-6 lg:divide-y-0 lg:pb-6">
                    <div class="pb-6 lg:pb-0">
                        <fieldset>
                            <legend class="mb-3 text-title-m">Jenis layanan</legend>
                            <div class="space-y-2">
                                @foreach (\App\Enums\JenisLayanan::cases() as $layanan)
                                    <x-ui.opsi-baris nama="jenis_layanan" :nilai="$layanan->value" :judul="$layanan->label()" :deskripsi="$layanan->deskripsi()"
                                        :ikon="$layanan->ikon()"
                                        :terpilih="($draf['jenis_layanan'] ?? \App\Enums\JenisLayanan::Darurat->value) === $layanan->value" />
                                @endforeach
                            </div>
                            @error('jenis_layanan')<p class="mt-1 {{ \App\Support\Tampilan::GALAT_ISIAN }}">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>

                    <div class="py-6 lg:py-0">
                        <fieldset>
                            <legend class="mb-3 text-title-m">Tingkat darurat</legend>
                            <x-ui.segmented label="Tingkat darurat">
                                @foreach (\App\Enums\TingkatDarurat::cases() as $tingkat)
                                    <x-ui.segmen nama="tingkat_darurat" :nilai="$tingkat->value"
                                        :terpilih="($draf['tingkat_darurat'] ?? \App\Enums\TingkatDarurat::Biasa->value) === $tingkat->value">{{ $tingkat->label() }}</x-ui.segmen>
                                @endforeach
                            </x-ui.segmented>
                            <div class="mt-3 space-y-1.5">
                                @foreach (\App\Enums\TingkatDarurat::cases() as $tingkat)
                                    <p class="text-body-s text-on-surface-variant"><span class="text-label-l text-on-surface">{{ $tingkat->label() }}.</span> {{ $tingkat->deskripsi() }}</p>
                                @endforeach
                            </div>
                            @error('tingkat_darurat')<p class="mt-1 {{ \App\Support\Tampilan::GALAT_ISIAN }}">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>
                </div>

                <div class="py-6">
                    <fieldset>
                        <legend class="mb-3 text-title-m">Lokasi jemput</legend>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 lg:grid-cols-2">
                            <x-peta.pemilih :nilai-lat="$draf['lat_jemput'] ?? null" :nilai-lng="$draf['lng_jemput'] ?? null" tinggi="h-64 lg:h-48" />
                            <x-ui.isian nama="lokasi_jemput" label="Alamat lokasi jemput" :nilai="$draf['lokasi_jemput'] ?? null" bantuan="Tulis nama jalan, patokan, atau kelurahan. Terisi otomatis bila Anda memilih titik di peta." :wajib="true" />
                        </div>
                    </fieldset>
                </div>

                {{-- Di lg ke atas, Faskes tujuan dan Data pasien berdampingan (pola sama dengan baris pertama). --}}
                <div class="divide-y divide-outline-variant lg:grid lg:grid-cols-2 lg:gap-x-6 lg:divide-y-0 lg:pt-6">
                    <div class="py-6 lg:py-0">
                        <fieldset>
                            <legend class="mb-3 text-title-m">Faskes tujuan</legend>
                            <x-ui.pilihan nama="faskes_tujuan_id" label="Faskes tujuan"
                                :opsi="$faskes->mapWithKeys(fn ($f) => [$f->id => $f->nama.' ('.$f->jenis->label().')'])->all()"
                                :nilai="$draf['faskes_tujuan_id'] ?? null" kosong="Belum ditentukan"
                                bantuan="Wajib untuk rujukan. Untuk layanan lain boleh dikosongkan." />
                            <div class="mt-3">
                                @foreach ($faskes as $f)
                                    <div hidden data-faskes="{{ $f->id }}" class="rounded-md border border-outline-variant bg-surface-container-lowest p-3">
                                        <x-ui.ketersediaan :faskes="$f" />
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>

                    <div class="pt-6 lg:pt-0">
                        <fieldset>
                            <legend class="mb-3 text-title-m">Data pasien</legend>
                            <div class="space-y-5">
                                <x-ui.isian nama="nama_pasien" label="Nama pasien" :nilai="$draf['nama_pasien'] ?? null" :wajib="true" autocomplete="off" />
                                <x-ui.isian nama="no_telepon_kontak" label="Nomor kontak yang bisa dihubungi" tipe="tel" :nilai="$noTeleponAwal" :wajib="true" inputmode="tel" />
                                <x-ui.area-teks nama="catatan" label="Catatan untuk petugas" :nilai="$draf['catatan'] ?? null" bantuan="Contoh: kondisi pasien, patokan rumah, atau pintu masuk." />
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>

            {{-- Desktop dan tablet (md ke atas): tombol di kanan bawah panel, lekat di dasar panel mulai lg. --}}
            <div class="mt-6 hidden items-center justify-end border-t border-outline-variant pt-5 md:flex lg:sticky lg:bottom-0 lg:z-10 lg:-mx-6 lg:-mb-6 lg:rounded-b-xl lg:bg-surface-container-lowest lg:px-6 lg:py-4">
                <x-ui.tombol tipe="submit" ukuran="lg">Tinjau pesanan</x-ui.tombol>
            </div>
        </form>
    </x-ui.panel>

    {{-- Ponsel (di bawah md): bar aksi lekat ke dasar layar, selebar penuh, lepas dari padding kartu dan halaman. --}}
    <div class="sticky bottom-0 -mx-4 mt-6 border-t border-outline-variant bg-surface-container-lowest px-4 py-3 md:hidden">
        <x-ui.tombol tipe="submit" form="form-buat-pesanan" ukuran="lg" class="w-full">Tinjau pesanan</x-ui.tombol>
    </div>

    @push('skrip')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const pilih = document.getElementById('f-faskes_tujuan_id');
                const tampil = () => document.querySelectorAll('[data-faskes]').forEach((el) => {
                    el.hidden = el.dataset.faskes !== pilih.value;
                });
                pilih.addEventListener('change', tampil);
                tampil();
            });
        </script>
    @endpush
</x-layouts.app>
