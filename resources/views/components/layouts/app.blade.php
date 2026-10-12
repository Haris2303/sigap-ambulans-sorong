@props(['judul' => null, 'peta' => false])
{{-- Kerangka aplikasi arah B (docs/RETEMA_B.md bagian 5): akar bg-surface p-4; lg ke atas bilah samping putih melayang 248px
     (sticky) dan kolom isi; di bawah lg bilah atas melayang dan laci modal (<dialog>, fokus terjebak bawaan peramban). --}}
@php
    $pengguna = auth()->user();
    $peran = $pengguna->role->value;
    $menu = config('sigap.menu.'.$peran, []);
    $urlRingkasan = $pengguna->role === \App\Enums\Role::Admin ? route('admin.pesanan.ringkasan') : null;
@endphp
<x-layouts.dasar :judul="$judul" :peta="$peta">
    <div class="min-h-screen bg-surface p-4 lg:flex lg:items-start lg:gap-5" @if ($urlRingkasan) data-ringkasan-url="{{ $urlRingkasan }}" data-detik="{{ config('sigap.polling.admin_detik') }}" @endif>
        {{-- Bilah atas ponsel dan tablet (RETEMA_B.md 5.2): kartu melayang, sticky. --}}
        <div class="sticky top-4 z-20 mb-4 flex items-center gap-2.5 rounded-xl bg-surface-container-lowest p-2 shadow-card lg:hidden">
            <button type="button" aria-label="Buka menu" data-menu-buka class="lapisan-keadaan inline-flex size-12 shrink-0 items-center justify-center rounded-lg bg-surface text-on-surface-variant">
                <x-ui.ikon nama="menu" :ukuran="21" />
            </button>
            <a href="{{ route($pengguna->role->ruteBeranda()) }}" class="flex min-w-0 flex-1 items-center gap-2.5">
                <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">
                    <x-ui.ikon nama="plus" :ukuran="20" />
                </span>
                <span class="min-w-0 truncate text-brand">SIGAP</span>
            </a>
            @if ($lonceng = config('sigap.bilah_atas.lonceng.'.$peran))
                @php($jumlahLonceng = $lencanaMenu[$lonceng['lencana']] ?? 0)
                <a href="{{ route($lonceng['route'], $lonceng['query'] ?? []) }}" data-lonceng="{{ $lonceng['lencana'] }}" data-label="{{ $lonceng['label'] }}" aria-label="{{ $lonceng['label'] }}, {{ $jumlahLonceng }}"
                    class="lapisan-keadaan relative inline-flex size-12 shrink-0 items-center justify-center rounded-lg text-on-surface-variant">
                    <x-ui.ikon nama="bell" :ukuran="21" />
                    <span data-sembunyi-nol="{{ $lonceng['lencana'] }}" @if ($jumlahLonceng < 1) hidden @endif aria-hidden="true" class="absolute right-2.5 top-2.5 size-2.5 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
                </a>
            @endif
            <x-ui.avatar :nama="$pengguna->name" />
        </div>

        {{-- Laci menu modal (<dialog>): lebar 316px, melayang 12px dari tepi, fokus terjebak dan Esc bawaan peramban. --}}
        {{-- open:flex, bukan flex: dialog tanpa atribut open disembunyikan peramban (dialog:not([open]){display:none}),
             tetapi gaya penulis selalu mengalahkan gaya bawaan peramban terlepas dari spesifisitas, sehingga "flex"
             tanpa syarat akan membuatnya tampil terus walau tertutup. "open:" (varian bawaan Tailwind untuk [open])
             menjaga itu tetap none sampai showModal() benar-benar membuka dialog. --}}
        <dialog data-laci aria-label="Menu" class="fixed inset-y-3 left-3 m-0 h-auto max-h-none w-79 open:flex flex-col overflow-y-auto rounded-2xl bg-surface-container-lowest p-4 shadow-overlay gulir-tipis backdrop:bg-scrim/45 lg:hidden">
            <x-layouts.menu-app :pengguna="$pengguna" :menu="$menu" :lencana-menu="$lencanaMenu" :laci="true" />
        </dialog>

        {{-- Bilah samping desktop (RETEMA_B.md 5.1): 248px, sticky, satu bilah gulir. --}}
        <aside aria-label="Bilah samping" data-shot="samping" class="sticky top-4 hidden h-[calc(100vh-2rem)] w-62 shrink-0 flex-col rounded-2xl bg-surface-container-lowest px-4 py-5.5 shadow-card lg:flex">
            <x-layouts.menu-app :pengguna="$pengguna" :menu="$menu" :lencana-menu="$lencanaMenu" />
        </aside>

        <main id="isi" class="min-w-0 flex-1 lg:max-w-screen-2xl lg:pb-6 lg:pr-2 lg:pt-1.5">
            <x-ui.flash />
            {{ $slot }}
        </main>
    </div>
</x-layouts.dasar>
