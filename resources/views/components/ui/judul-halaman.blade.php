@props(['judul', 'deskripsi' => null])
{{-- Kepala halaman arah B (docs/RETEMA_B.md 5.1): judul kiri (page-title, subjudul body-m), kanan slot aksi per
     halaman lalu, hanya di lg ke atas dan bila peran memakainya (config sigap.bilah_atas), tombol cari dan lonceng.
     Di bawah lg pencarian dan lonceng sudah ada di bilah atas (x-layouts.app), sehingga tidak diulang di sini. --}}
@php
    $pengguna = auth()->user();
    $peran = $pengguna?->role?->value;
    $aksiCari = $peran ? config('sigap.bilah_atas.cari.'.$peran) : null;
    $lonceng = $peran ? config('sigap.bilah_atas.lonceng.'.$peran) : null;
    $jumlahLonceng = $lonceng ? ((isset($lencanaMenu) ? $lencanaMenu[$lonceng['lencana']] ?? 0 : 0)) : 0;
    $idCari = 'dialog-cari-pesanan';
@endphp
<header class="mb-5 flex flex-col items-start gap-3.5 lg:flex-row lg:items-center">
    <div class="min-w-0 flex-1">
        <h1 class="text-page-title max-lg:text-page-title-s">{{ $judul }}</h1>
        @if ($deskripsi)
            <p class="mt-1 text-body-m text-on-surface-variant">{{ $deskripsi }}</p>
        @endif
    </div>

    <div class="flex w-full shrink-0 items-center gap-2 lg:w-auto">
        @isset($aksi)
            <div class="flex flex-wrap items-center justify-end gap-2">{{ $aksi }}</div>
        @endisset

        @if ($aksiCari)
            <button type="button" aria-label="Cari pesanan" data-cari-buka="{{ $idCari }}"
                class="lapisan-keadaan hidden size-12 shrink-0 items-center justify-center rounded-lg bg-surface-container-lowest text-on-surface-variant shadow-control lg:inline-flex">
                <x-ui.ikon nama="search" :ukuran="21" />
            </button>
        @endif

        @if ($lonceng)
            <a href="{{ route($lonceng['route'], $lonceng['query'] ?? []) }}" data-lonceng="{{ $lonceng['lencana'] }}" data-label="{{ $lonceng['label'] }}" aria-label="{{ $lonceng['label'] }}, {{ $jumlahLonceng }}"
                class="lapisan-keadaan relative hidden size-12 shrink-0 items-center justify-center rounded-lg bg-surface-container-lowest text-on-surface-variant shadow-control lg:inline-flex">
                <x-ui.ikon nama="bell" :ukuran="21" />
                <span data-sembunyi-nol="{{ $lonceng['lencana'] }}" @if ($jumlahLonceng < 1) hidden @endif aria-hidden="true" class="absolute right-2.5 top-2.5 size-2.5 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
            </a>
        @endif
    </div>
</header>

@if ($aksiCari)
    <dialog id="{{ $idCari }}" aria-labelledby="{{ $idCari }}-judul" data-dialog-cari class="m-auto w-full max-w-md rounded-2xl bg-surface-container-lowest p-6 text-on-surface shadow-overlay backdrop:bg-scrim/45">
        <h2 id="{{ $idCari }}-judul" class="text-title-l">Cari pesanan</h2>
        <form method="GET" action="{{ route($aksiCari) }}" role="search" class="mt-4 flex items-center gap-2">
            <label for="{{ $idCari }}-q" class="sr-only">Cari kode pesanan atau nama pasien</label>
            <input id="{{ $idCari }}-q" type="search" name="q" placeholder="Kode pesanan atau nama pasien" value="{{ request()->query('q') }}"
                class="{{ \App\Support\Tampilan::kelasIsian(false) }}">
            <x-ui.tombol tipe="submit">Cari</x-ui.tombol>
        </form>
    </dialog>
@endif
