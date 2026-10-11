@php
    // $errors tidak ada pada halaman galat yang dirender di luar middleware web.
    $galatForm = $errors ?? new \Illuminate\Support\ViewErrorBag;
@endphp
@if (session('sukses') || session('galat') || $galatForm->any())
    <div class="mb-6 space-y-3">
        @if (session('sukses'))
            <x-ui.peringatan nada="sukses">{{ session('sukses') }}</x-ui.peringatan>
        @endif
        @if (session('galat'))
            <x-ui.peringatan nada="bahaya">{{ session('galat') }}</x-ui.peringatan>
        @endif
        @if ($galatForm->any())
            <x-ui.peringatan nada="bahaya">
                <p>Periksa kembali isian berikut.</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($galatForm->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </x-ui.peringatan>
        @endif
    </div>
@endif
