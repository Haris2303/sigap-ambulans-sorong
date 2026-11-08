<x-layouts.app judul="Ketersediaan">
    <x-ui.judul-halaman judul="Ketersediaan" deskripsi="Bed dan dokter di setiap Faskes aktif. Simpan per baris; waktu pembaruan tercatat otomatis." />

    @if ($daftar->isEmpty())
        <x-ui.kosong pesan="Belum ada Faskes aktif." />
    @else
    <x-ui.panel :flush="true">
        <div class="p-5 lg:p-6">
        <x-ui.tabel :bingkai="false" lebar-min="min-w-200">
            <x-slot:kepala>
                <th>Faskes</th>
                <th>Bed IGD (tersedia, total)</th>
                <th>Bed rawat inap (tersedia, total)</th>
                <th>Dokter umum</th>
                <th>Dokter spesialis</th>
                <th>Diperbarui</th>
                <th><span class="sr-only">Aksi</span></th>
            </x-slot:kepala>
            @foreach ($daftar as $f)
                @php $form = 'form-ketersediaan-'.$f->id; @endphp
                <tr>
                    <td class="align-top"><p class="text-label-l">{{ $f->nama }}</p><p class="text-body-s text-on-surface-variant">{{ $f->jenis->label() }}</p></td>
                    <td>
                        <div class="flex gap-2">
                            <div class="w-20"><x-ui.isian nama="bed_igd_tersedia" :id-unik="$form.'-igd-tersedia'" label="Bed IGD tersedia {{ $f->nama }}" :label-tersembunyi="true" :tanpa-old="true" tipe="number" min="0" :nilai="$f->bed_igd_tersedia" :form="$form" /></div>
                            <div class="w-20"><x-ui.isian nama="bed_igd_total" :id-unik="$form.'-igd-total'" label="Bed IGD total {{ $f->nama }}" :label-tersembunyi="true" :tanpa-old="true" tipe="number" min="0" :nilai="$f->bed_igd_total" :form="$form" /></div>
                        </div>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <div class="w-20"><x-ui.isian nama="bed_rawat_inap_tersedia" :id-unik="$form.'-inap-tersedia'" label="Bed rawat inap tersedia {{ $f->nama }}" :label-tersembunyi="true" :tanpa-old="true" tipe="number" min="0" :nilai="$f->bed_rawat_inap_tersedia" :form="$form" /></div>
                            <div class="w-20"><x-ui.isian nama="bed_rawat_inap_total" :id-unik="$form.'-inap-total'" label="Bed rawat inap total {{ $f->nama }}" :label-tersembunyi="true" :tanpa-old="true" tipe="number" min="0" :nilai="$f->bed_rawat_inap_total" :form="$form" /></div>
                        </div>
                    </td>
                    <td><div class="w-20"><x-ui.isian nama="dokter_umum_tersedia" :id-unik="$form.'-umum'" label="Dokter umum {{ $f->nama }}" :label-tersembunyi="true" :tanpa-old="true" tipe="number" min="0" :nilai="$f->dokter_umum_tersedia" :form="$form" /></div></td>
                    <td><div class="w-20"><x-ui.isian nama="dokter_spesialis_tersedia" :id-unik="$form.'-spesialis'" label="Dokter spesialis {{ $f->nama }}" :label-tersembunyi="true" :tanpa-old="true" tipe="number" min="0" :nilai="$f->dokter_spesialis_tersedia" :form="$form" /></div></td>
                    <td class="align-top">
                        @if ($f->belumDiperbarui())
                            <x-ui.lencana nada="peringatan">Belum diperbarui</x-ui.lencana>
                        @else
                            <x-ui.waktu :nilai="$f->ketersediaan_diperbarui_pada" class="text-body-s" />
                            @if ($f->dataLama())
                                <x-ui.lencana nada="peringatan">Data lama</x-ui.lencana>
                            @endif
                        @endif
                    </td>
                    <td>
                        <form id="{{ $form }}" method="POST" action="{{ route('admin.ketersediaan.update', $f) }}">
                            @csrf
                            @method('PUT')
                            <x-ui.tombol nada="sekunder" tipe="submit" :form="$form">Simpan</x-ui.tombol>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-ui.tabel>
        </div>
    </x-ui.panel>
    @endif
</x-layouts.app>
