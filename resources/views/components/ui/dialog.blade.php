@props([
    'id' => 'dialog-konfirmasi',
    'judul' => 'Konfirmasi',
    'pesan' => '',
    'aksi' => 'Lanjutkan',
    'nada' => 'bahaya',
])
{{-- Satu instance dipakai bersama semua elemen data-konfirmasi (diisi sigap.js). --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" class="m-auto w-full max-w-md rounded-lg border border-line bg-surface p-6 text-on-surface shadow-overlay backdrop:bg-scrim">
    <h2 id="{{ $id }}-judul" class="text-h2" data-dialog-judul>{{ $judul }}</h2>
    <p class="mt-2 text-body-sm text-on-surface-muted" data-dialog-pesan>{{ $pesan }}</p>
    <div class="mt-6 flex justify-end gap-2">
        <x-ui.tombol nada="hantu" data-dialog-batal>Batal</x-ui.tombol>
        <x-ui.tombol :nada="$nada === 'bahaya' ? 'bahaya' : 'utama'" data-dialog-aksi>{{ $aksi }}</x-ui.tombol>
    </div>
</dialog>
