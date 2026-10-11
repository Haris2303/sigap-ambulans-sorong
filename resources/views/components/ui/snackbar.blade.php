@props(['tetap' => true])
{{-- Snackbar arah B (DESIGN.md 6.11): hasil tindakan; tetap tampil sampai ditutup (tanpa batas waktu otomatis). tetap=false hanya untuk katalog. --}}
<div role="status" data-snackbar {{ $attributes->merge(['class' => ($tetap ? 'fixed bottom-4 left-4 right-4 z-50 md:left-auto md:w-96 ' : 'max-w-96 ').'flex items-center gap-2 rounded-md bg-inverse-surface py-1 pl-4 pr-2 text-body-m text-inverse-on-surface shadow-overlay']) }}>
    <p class="min-w-0 flex-1 py-2">{{ $slot }}</p>
    <x-ui.tombol nada="terbalik" data-snackbar-tutup>Tutup</x-ui.tombol>
</div>
