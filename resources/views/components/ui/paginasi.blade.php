@props(['data'])
@if ($data->total() > 0)
    @php
        $tautan = $data->linkCollection()->values();
        $akhir = $tautan->count() - 1;
        $kelasDasar = 'inline-flex min-h-10 min-w-10 items-center justify-center rounded-full px-3 text-label-l transition-colors';
    @endphp
    <nav {{ $attributes->merge(['class' => 'mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }} aria-label="Halaman">
        <p class="text-body-m text-on-surface-variant">Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }}</p>
        @if ($data->hasPages())
            <ul class="flex flex-wrap items-center gap-1" role="list">
                @foreach ($tautan as $i => $item)
                    @php
                        $teks = $i === 0 ? 'Sebelumnya' : ($i === $akhir ? 'Berikutnya' : $item['label']);
                    @endphp
                    <li>
                        @if ($item['url'] === null)
                            <span class="{{ $kelasDasar }} {{ $item['label'] === '...' ? 'text-on-surface-variant' : 'text-on-surface/38' }}">{{ $teks }}</span>
                        @elseif ($item['active'])
                            <span class="{{ $kelasDasar }} bg-secondary-container text-on-secondary-container" aria-current="page">{{ $teks }}</span>
                        @else
                            <a href="{{ $item['url'] }}" class="lapisan-keadaan target-sentuh {{ $kelasDasar }} text-on-surface-variant">{{ $teks }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </nav>
@endif
