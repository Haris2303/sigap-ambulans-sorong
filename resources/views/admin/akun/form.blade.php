<x-layouts.app :judul="($baru ? 'Tambah ' : 'Ubah ').$judulAkun">
    <x-ui.judul-halaman :judul="$baru ? 'Tambah '.$judulAkun : 'Ubah '.$akun->name" />

    <x-ui.panel class="max-w-5xl">
        <form method="POST" action="{{ $baru ? route($rute.'.store') : route($rute.'.update', $akun) }}" id="form-akun">
            @csrf
            @unless ($baru)
                @method('PUT')
            @endunless
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <x-ui.isian nama="name" label="Nama lengkap" :nilai="$akun->name" :wajib="true" />
                <x-ui.isian nama="email" label="Email" tipe="email" :nilai="$akun->email" :wajib="true" />
                <x-ui.isian nama="no_telepon" label="Nomor telepon" tipe="tel" :nilai="$akun->no_telepon" />
                <x-ui.pilihan nama="faskes_id" label="Faskes" :opsi="$opsiFaskes" :nilai="$akun->faskes_id" kosong="Pilih Faskes" :wajib="true" />
                <x-ui.isian nama="password" label="Kata sandi" tipe="password" :wajib="$baru"
                    :bantuan="$baru ? 'Paling sedikit 8 karakter.' : 'Kosongkan bila tidak diubah.'" autocomplete="new-password" />
                <x-ui.centang nama="aktif" label="Akun aktif" :nilai="$akun->aktif" />
            </div>

            <div class="mt-6 hidden items-center justify-end gap-2 border-t border-outline-variant pt-5 md:flex lg:sticky lg:bottom-0 lg:z-10 lg:-mx-6 lg:-mb-6 lg:rounded-b-xl lg:bg-surface-container-lowest lg:px-6 lg:py-4">
                <x-ui.tombol nada="hantu" :href="route($rute.'.index')">Batal</x-ui.tombol>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
            </div>
        </form>
    </x-ui.panel>

    <div class="sticky bottom-0 -mx-4 mt-6 border-t border-outline-variant bg-surface-container-lowest px-4 py-3 md:hidden">
        <x-ui.tombol tipe="submit" form="form-akun" ukuran="lg" class="w-full">Simpan</x-ui.tombol>
    </div>
</x-layouts.app>
