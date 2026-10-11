{{-- Satu wadah berbingkai; sel dipisah garis 1px (gap di atas latar outline-variant). Gunakan jumlah sel genap. --}}
<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-px overflow-hidden rounded-md border border-outline-variant bg-outline-variant lg:grid-cols-4']) }}>
    {{ $slot }}
</div>
