{{-- Slot kepala berisi <th>, slot isi berisi <tr> dengan <td>. --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-md border border-outline-variant bg-surface-container-lowest']) }}>
    <table class="w-full text-left text-body-m">
        <thead class="bg-surface-container-low text-label-l text-on-surface-variant">
            <tr class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-left [&>th]:font-medium">{{ $kepala }}</tr>
        </thead>
        <tbody class="divide-y divide-outline-variant [&>tr:hover]:bg-on-surface/8 [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle">{{ $slot }}</tbody>
    </table>
</div>
