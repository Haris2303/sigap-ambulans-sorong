@props(['bingkai' => true, 'lebarMin' => null])
{{-- Slot kepala berisi <th>, slot isi berisi <tr> dengan <td>. bingkai=false: tanpa batas dan latar sendiri, untuk tabel di dalam kartu tanpa padding (kolom pertama dan terakhir sejajar padding kartu 24px). lebar-min: kelas min-w-* agar tabel bergulir di ponsel. --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto'.($bingkai ? ' rounded-md border border-outline-variant bg-surface-container-lowest' : '')]) }}>
    <table class="w-full text-left text-body-m {{ $lebarMin }}">
        <thead class="bg-surface-container-low text-label-l text-on-surface-variant">
            <tr class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-left [&>th]:font-medium {!! $bingkai ? '' : '[&>th:first-child]:pl-6 [&>th:last-child]:pr-6' !!}">{{ $kepala }}</tr>
        </thead>
        <tbody class="divide-y divide-outline-variant [&>tr:hover]:bg-on-surface/8 [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle {!! $bingkai ? '' : '[&_td:first-child]:pl-6 [&_td:last-child]:pr-6' !!}">{{ $slot }}</tbody>
    </table>
</div>
