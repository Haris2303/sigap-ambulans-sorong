{{-- Satu wadah berbingkai; sel dipisah garis 1px (gap di atas latar line). Gunakan jumlah sel genap. --}}
<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-line bg-line lg:grid-cols-4']) }}>
    {{ $slot }}
</div>
