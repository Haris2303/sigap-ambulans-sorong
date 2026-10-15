@props(['judul' => null])
{{-- Layout masuk arah B (docs/RETEMA_B.md 5.3): satu kartu putih di tengah (rounded-2xl, shadow-card, max-w-md),
     merek (petak 42px + "SIGAP" text-brand) dan flash di dalam kartu, di atas isi ($slot). --}}
<x-layouts.dasar :judul="$judul">
    <main id="isi" class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-8">
        <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-card lg:p-8">
            <a href="{{ route('beranda') }}" class="mb-6 flex items-center gap-3 text-on-surface">
                <span class="inline-flex size-10.5 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">
                    <x-ui.ikon nama="plus" :ukuran="24" />
                </span>
                <span class="text-brand">SIGAP</span>
            </a>
            <x-ui.flash />
            {{ $slot }}
        </div>
    </main>
</x-layouts.dasar>
