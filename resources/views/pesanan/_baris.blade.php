{{--
    Baris daftar pesanan arah B (docs/RETEMA_B.md 6.9, acuan kartu "Pesanan terbaru" B1-dasbor.html): tabel di lg ke
    atas, daftar baris di bawah md. Satu partial dipakai admin.dashboard, admin.pesanan.index, petugas.pesanan.index,
    dan warga.pesanan.index supaya pola baris sama persis di semua tempat (bukan ditulis ulang tiap halaman).

    Variabel:
    - $daftar: koleksi Pesanan (dengan relasi pemohon dimuat bila perlu di tempat lain; partial ini hanya memakai
      kolom Pesanan sendiri, tidak memuat relasi tambahan).
    - $rute: nama route "show" pesanan untuk peran yang melihat (admin.pesanan.show, petugas.pesanan.show, warga.pesanan.show).
    - $pesanKosong (opsional): teks x-ui.kosong saat $daftar kosong. Bawaan "Belum ada pesanan di tab ini."
--}}
@php $pesanKosong ??= 'Belum ada pesanan di tab ini.'; @endphp
@if ($daftar->isEmpty())
    <x-ui.kosong :pesan="$pesanKosong" />
@else
    <x-ui.tabel :bingkai="false" class="hidden md:block">
        <x-slot:kepala>
            <th>Pasien</th>
            <th>Layanan</th>
            <th>Status</th>
            <th>Jam</th>
            <th><span class="sr-only">Buka</span></th>
        </x-slot:kepala>
        @foreach ($daftar as $pesanan)
            <tr class="relative">
                <td>
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :nama="$pesanan->nama_pasien" />
                        <div class="min-w-0">
                            <p class="truncate text-label-l"><a href="{{ route($rute, $pesanan) }}" class="after:absolute after:inset-0">{{ $pesanan->nama_pasien }}</a></p>
                            <p class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->kode }}</p>
                            @if ($pesanan->status->menungguPenugasan())
                                <x-ui.waktu-tunggu :pesanan="$pesanan" class="mt-1" />
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <span class="inline-flex flex-wrap items-center gap-2">
                        {{ $pesanan->jenis_layanan->label() }}
                        @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                            <x-ui.lencana nada="bahaya" size="sm">{{ \App\Enums\TingkatDarurat::Gawat->label() }}</x-ui.lencana>
                        @endif
                    </span>
                </td>
                <td><x-ui.lencana-status :status="$pesanan->status" /></td>
                <td class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->created_at->format('H.i') }}</td>
                <td aria-hidden="true"><x-ui.ikon nama="chevron-right" :ukuran="18" class="text-on-surface-variant" /></td>
            </tr>
        @endforeach
    </x-ui.tabel>

    {{-- Daftar baris di bawah md (docs/RETEMA_B.md 6.9). --}}
    <ul role="list" class="md:hidden">
        @foreach ($daftar as $pesanan)
            <li class="relative grid grid-cols-[40px_1fr_auto] items-center gap-3 border-t border-surface-container-highest py-3 first:border-t-0">
                <x-ui.avatar :nama="$pesanan->nama_pasien" />
                <div class="min-w-0">
                    <p class="truncate text-label-l"><a href="{{ route($rute, $pesanan) }}" class="after:absolute after:inset-0">{{ $pesanan->nama_pasien }}</a></p>
                    <p class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->kode }}, {{ $pesanan->jenis_layanan->label() }}</p>
                    @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                        <x-ui.lencana nada="bahaya" size="sm" class="mt-1">{{ \App\Enums\TingkatDarurat::Gawat->label() }}</x-ui.lencana>
                    @endif
                    @if ($pesanan->status->menungguPenugasan())
                        <x-ui.waktu-tunggu :pesanan="$pesanan" class="mt-1" />
                    @endif
                </div>
                <div class="flex flex-col items-end gap-1">
                    <x-ui.lencana-status :status="$pesanan->status" size="sm" />
                    <span class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->created_at->format('H.i') }}</span>
                </div>
            </li>
        @endforeach
    </ul>
@endif
