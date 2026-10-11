@props(['judul', 'terbuka' => false])
{{-- Pengungkap berbasis <details> bawaan, tanpa JavaScript: aksi sekunder atau berisiko yang tidak perlu terbuka terus (mis. "Tolak penugasan" beserta kolom alasan). terbuka: tampil terbuka, mis. saat ada galat isian di dalamnya. --}}
<details @if ($terbuka) open @endif {{ $attributes->merge(['class' => 'group rounded-md border border-outline-variant']) }}>
    <summary class="lapisan-keadaan flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 rounded-md px-4 text-label-l text-on-surface-variant [&::-webkit-details-marker]:hidden">
        <span>{{ $judul }}</span>
        <x-ui.ikon nama="chevron-down" :ukuran="20" class="group-open:rotate-180" />
    </summary>
    <div class="border-t border-outline-variant p-4">{{ $slot }}</div>
</details>
