@props([
    'id' => 'dialog-konfirmasi',
    'judul' => 'Konfirmasi',
    'pesan' => '',
    'aksi' => 'Lanjutkan',
    'nada' => 'bahaya',
])
{{-- Satu instance dipakai bersama semua elemen data-konfirmasi (diisi sigap.js). --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" class="m-auto w-full max-w-140 rounded-xl bg-surface-container-high p-6 text-on-surface shadow-level-3 backdrop:bg-scrim">
    <h2 id="{{ $id }}-judul" class="text-headline-s" data-dialog-judul>{{ $judul }}</h2>
    <p class="mt-4 text-body-m text-on-surface-variant" data-dialog-pesan>{{ $pesan }}</p>
    <div class="mt-6 flex justify-end gap-2">
        <x-ui.tombol nada="hantu" data-dialog-batal>Batal</x-ui.tombol>
        <x-ui.tombol :nada="$nada === 'bahaya' ? 'bahaya' : 'hantu'" data-dialog-aksi>{{ $aksi }}</x-ui.tombol>
    </div>
</dialog>
