{{-- Fragmen: tabel pesanan milik Masyarakat. Variabel: $daftar (koleksi Pesanan). --}}
<x-ui.tabel>
    <x-slot:kepala>
        <th>Kode</th>
        <th>Layanan</th>
        <th>Status</th>
        <th class="hidden md:table-cell">Dibuat</th>
    </x-slot:kepala>
    @foreach ($daftar as $pesanan)
        <tr>
            <td class="tabular-nums"><a href="{{ route('warga.pesanan.show', $pesanan) }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->kode }}</a></td>
            <td><span class="inline-flex items-center gap-2">{{ $pesanan->jenis_layanan->label() }}@if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat) <x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana>@endif</span></td>
            <td><x-ui.lencana-status :status="$pesanan->status" /></td>
            <td class="hidden md:table-cell"><x-ui.waktu :nilai="$pesanan->created_at" /></td>
        </tr>
    @endforeach
</x-ui.tabel>
