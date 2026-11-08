<x-layouts.app :judul="$baru ? 'Tambah Ambulans' : 'Ubah Ambulans'">
    <x-ui.judul-halaman :judul="$baru ? 'Tambah Ambulans' : 'Ubah '.$ambulans->plat_nomor" />

    <x-ui.panel class="max-w-5xl">
        <form method="POST" action="{{ $baru ? route('admin.ambulans.store') : route('admin.ambulans.update', $ambulans) }}" id="form-ambulans">
            @csrf
            @unless ($baru)
                @method('PUT')
            @endunless
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <x-ui.isian nama="plat_nomor" label="Plat nomor" :nilai="$ambulans->plat_nomor" :wajib="true" />
                <x-ui.pilihan nama="faskes_id" label="Faskes" :opsi="$opsiFaskes" :nilai="$ambulans->faskes_id" kosong="Pilih Faskes" :wajib="true" />
                <x-ui.pilihan nama="jenis" label="Jenis" :opsi="\App\Enums\JenisAmbulans::opsi()" :nilai="$ambulans->jenis" kosong="Pilih jenis" :wajib="true" />
                <x-ui.isian nama="merek_model" label="Merek dan model" :nilai="$ambulans->merek_model" />
                <x-ui.pilihan nama="sopir_id" label="Sopir tetap" :opsi="$opsiSopir" :nilai="$ambulans->sopir_id" kosong="Tanpa sopir tetap" bantuan="Sopir harus berasal dari Faskes yang sama dengan ambulans." />
                @if ($ambulans->status === \App\Enums\StatusAmbulans::Bertugas)
                    <div>
                        <p class="mb-2 text-label-l text-on-surface">Status</p>
                        <x-ui.lencana :nada="$ambulans->status->nada()">{{ $ambulans->status->label() }}</x-ui.lencana>
                        <p class="mt-1 text-body-s text-on-surface-variant">Status berubah otomatis setelah pesanan selesai atau dibatalkan.</p>
                    </div>
                @else
                    <x-ui.pilihan nama="status" label="Status" :opsi="\App\Enums\StatusAmbulans::opsiManual()" :nilai="$ambulans->status" />
                @endif
            </div>

            <div class="mt-6 hidden items-center justify-end gap-2 border-t border-outline-variant pt-5 md:flex lg:sticky lg:bottom-0 lg:z-10 lg:-mx-6 lg:-mb-6 lg:rounded-b-xl lg:bg-surface-container-lowest lg:px-6 lg:py-4">
                <x-ui.tombol nada="hantu" :href="route('admin.ambulans.index')">Batal</x-ui.tombol>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
            </div>
        </form>
    </x-ui.panel>

    <div class="sticky bottom-0 -mx-4 mt-6 border-t border-outline-variant bg-surface-container-lowest px-4 py-3 md:hidden">
        <x-ui.tombol tipe="submit" form="form-ambulans" ukuran="lg" class="w-full">Simpan</x-ui.tombol>
    </div>
</x-layouts.app>
