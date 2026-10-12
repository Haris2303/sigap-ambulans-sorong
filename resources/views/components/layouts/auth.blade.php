@props(['judul' => null])
{{-- Layout masuk arah B (docs/RETEMA_B.md 5.3): kartu tengah max-w-sm, tanda merek 48px di atas judul. --}}
<x-layouts.dasar :judul="$judul">
    <main id="isi" class="mx-auto flex min-h-screen max-w-sm flex-col justify-center px-4 py-8">
        <a href="{{ route('beranda') }}" class="mx-auto mb-6 flex flex-col items-center gap-2 text-on-surface">
            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">
                <x-ui.ikon nama="plus" :ukuran="24" />
            </span>
            <span class="text-title-l">SIGAP Ambulans</span>
        </a>
        <x-ui.flash />
        {{ $slot }}
    </main>
</x-layouts.dasar>
