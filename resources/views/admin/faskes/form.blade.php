<x-layouts.app :judul="$baru ? 'Tambah Faskes' : 'Ubah Faskes'" :peta="true">
    <x-ui.judul-halaman :judul="$baru ? 'Tambah Faskes' : 'Ubah '.$faskes->nama" deskripsi="Titik koordinat dipakai untuk peta pada Info Faskes." />

    <x-ui.panel class="max-w-3xl">
        <form method="POST" action="{{ $baru ? route('admin.faskes.store') : route('admin.faskes.update', $faskes) }}" id="form-faskes">
            @csrf
            @unless ($baru)
                @method('PUT')
            @endunless
            <div class="space-y-5">
                <x-ui.isian nama="nama" label="Nama Faskes" :nilai="$faskes->nama" :wajib="true" />
                <x-ui.pilihan nama="jenis" label="Jenis" :opsi="\App\Enums\JenisFaskes::opsi()" :nilai="$faskes->jenis" kosong="Pilih jenis" :wajib="true" />
                <x-ui.pilihan nama="kepemilikan" label="Kepemilikan" :opsi="\App\Enums\Kepemilikan::opsi()" :nilai="$faskes->kepemilikan" kosong="Belum diketahui" />
                <x-ui.isian nama="telepon" label="Telepon" tipe="tel" :nilai="$faskes->telepon" />
                <x-ui.isian nama="alamat" label="Alamat" :nilai="$faskes->alamat" bantuan="Terisi otomatis bila Anda memilih titik di peta." />
                <div>
                    <p class="mb-2 text-label-l text-on-surface">Titik koordinat</p>
                    <x-peta.pemilih lat="lat" lng="lng" alamat="alamat" :nilai-lat="$faskes->lat" :nilai-lng="$faskes->lng" />
                </div>
                <x-ui.centang nama="aktif" label="Faskes aktif" :nilai="$faskes->aktif" />
            </div>

            <div class="mt-6 hidden items-center justify-end gap-2 border-t border-outline-variant pt-5 md:flex">
                <x-ui.tombol nada="hantu" :href="route('admin.faskes.index')">Batal</x-ui.tombol>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
            </div>
        </form>
    </x-ui.panel>

    <div class="sticky bottom-0 -mx-4 mt-6 border-t border-outline-variant bg-surface-container-lowest px-4 py-3 md:hidden">
        <x-ui.tombol tipe="submit" form="form-faskes" ukuran="lg" class="w-full">Simpan</x-ui.tombol>
    </div>
</x-layouts.app>
