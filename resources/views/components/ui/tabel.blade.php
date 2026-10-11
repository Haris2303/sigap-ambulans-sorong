@props(['bingkai' => true, 'lebarMin' => null])
{{-- x-ui.table arah B (DESIGN.md 6.9): baris bulat (border-spacing, bukan garis pemisah), hover surface-container-low.
     Slot kepala berisi <th>, slot isi berisi <tr> dengan <td>. bingkai=true: kartu berbayang, padding pada pembungkus
     supaya baris bulat tidak terpotong. bingkai=false: hanya pembungkus gulir, untuk tabel di dalam panel flush yang
     sudah ada. lebar-min: kelas min-w-* agar tabel bergulir di ponsel, bukan menyempit. --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto'.($bingkai ? ' '.\App\Support\Tampilan::KARTU.' p-3' : '')]) }}>
    <table class="w-full border-separate border-spacing-y-1 text-left text-body-m {{ $lebarMin }}">
        <thead class="text-label-m text-on-surface-variant">
            <tr class="[&>th]:px-2 [&>th]:py-2 [&>th]:text-left">{{ $kepala }}</tr>
        </thead>
        <tbody class="[&>tr]:transition-colors [&>tr:hover]:bg-surface-container-low [&_td]:px-2 [&_td]:py-3 [&_td]:align-middle [&_td:first-child]:rounded-l-lg [&_td:last-child]:rounded-r-lg">{{ $slot }}</tbody>
    </table>
</div>
