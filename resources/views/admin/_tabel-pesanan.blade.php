{{-- Fragmen: tabel pesanan untuk Dinkes di dalam kartu tanpa padding. Variabel: $daftar (koleksi Pesanan dengan pemohon). --}}
<x-ui.tabel :bingkai="false" lebar-min="min-w-136">
    <x-slot:kepala>
        <th>Kode</th>
        <th>Pasien</th>
        <th class="hidden md:table-cell">Layanan</th>
        <th>Status</th>
        <th class="hidden lg:table-cell">Dibuat</th>
    </x-slot:kepala>
    @foreach ($daftar as $pesanan)
        <tr>
            <td class="tabular-nums">
                <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->kode }}</a>
                {{-- Kolom Layanan (tempat lencana Gawat) tersembunyi di bawah md; di ponsel Gawat tampil di bawah kode. --}}
                @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                    <div class="mt-1 md:hidden"><x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana></div>
                @endif
                @if ($pesanan->status->menungguPenugasan())
                    <div class="mt-1"><x-ui.waktu-tunggu :pesanan="$pesanan" /></div>
                @endif
            </td>
            <td>{{ $pesanan->nama_pasien }}</td>
            <td class="hidden md:table-cell"><span class="inline-flex items-center gap-2">{{ $pesanan->jenis_layanan->label() }}@if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat) <x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana>@endif</span></td>
            <td><x-ui.lencana-status :status="$pesanan->status" /></td>
            <td class="hidden lg:table-cell"><x-ui.waktu :nilai="$pesanan->created_at" /></td>
        </tr>
    @endforeach
</x-ui.tabel>
