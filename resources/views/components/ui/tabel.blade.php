{{-- Slot kepala berisi <th>, slot isi berisi <tr> dengan <td>. --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-line bg-surface']) }}>
    <table class="w-full text-left text-body-sm">
        <thead class="bg-canvas text-label text-on-surface-muted">
            <tr class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-left [&>th]:font-medium">{{ $kepala }}</tr>
        </thead>
        <tbody class="divide-y divide-line [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle">{{ $slot }}</tbody>
    </table>
</div>
