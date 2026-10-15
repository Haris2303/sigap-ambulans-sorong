<x-layouts.app judul="Pesan ambulans" :peta="true">
    <x-ui.judul-halaman judul="Pesan ambulans" deskripsi="Isi data pesanan. Anda dapat memeriksanya kembali sebelum dikirim." />
    <x-ui.kontak-darurat varian="tautan" class="mb-6 max-w-3xl">Formulir tidak bisa dipakai?</x-ui.kontak-darurat>

    <x-ui.panel class="max-w-3xl">
        <form method="POST" action="{{ route('warga.pesanan.tinjau') }}" id="form-buat-pesanan">
            @csrf
            <div class="divide-y divide-outline-variant">
                <fieldset class="pb-6">
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

                <fieldset class="py-6">
                    <legend class="mb-3 text-title-m">Tingkat darurat</legend>
                    <x-ui.segmented label="Tingkat darurat" class="md:inline-flex">
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

                <fieldset class="space-y-3 py-6">
                    <legend class="mb-3 text-title-m">Lokasi jemput</legend>
                    <x-peta.pemilih :nilai-lat="$draf['lat_jemput'] ?? null" :nilai-lng="$draf['lng_jemput'] ?? null" />
                    <x-ui.isian nama="lokasi_jemput" label="Alamat lokasi jemput" :nilai="$draf['lokasi_jemput'] ?? null" bantuan="Tulis nama jalan, patokan, atau kelurahan. Terisi otomatis bila Anda memilih titik di peta." :wajib="true" />
                </fieldset>

                <fieldset class="py-6">
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

                <fieldset class="space-y-5 pt-6">
                    <legend class="mb-3 text-title-m">Data pasien</legend>
                    <x-ui.isian nama="nama_pasien" label="Nama pasien" :nilai="$draf['nama_pasien'] ?? null" :wajib="true" autocomplete="off" />
                    <x-ui.isian nama="no_telepon_kontak" label="Nomor kontak yang bisa dihubungi" tipe="tel" :nilai="$noTeleponAwal" :wajib="true" inputmode="tel" />
                    <x-ui.area-teks nama="catatan" label="Catatan untuk petugas" :nilai="$draf['catatan'] ?? null" bantuan="Contoh: kondisi pasien, patokan rumah, atau pintu masuk." />
                </fieldset>
            </div>

            {{-- Desktop dan tablet (md ke atas): tombol di kanan bawah panel. --}}
            <div class="mt-6 hidden items-center justify-end border-t border-outline-variant pt-5 md:flex">
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
