@props(['data'])
@if ($data->total() > 0)
    @php
        $tautan = $data->linkCollection()->values();
        $akhir = $tautan->count() - 1;
        $kelasDasar = 'inline-flex min-h-9 min-w-9 items-center justify-center rounded-md px-3 text-label transition-colors';
    @endphp
    <nav {{ $attributes->merge(['class' => 'mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }} aria-label="Halaman">
        <p class="text-body-sm text-on-surface-muted">Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }}</p>
        @if ($data->hasPages())
            <ul class="flex flex-wrap items-center gap-1" role="list">
                @foreach ($tautan as $i => $item)
                    @php
                        $teks = $i === 0 ? 'Sebelumnya' : ($i === $akhir ? 'Berikutnya' : $item['label']);
                    @endphp
                    <li>
                        @if ($item['url'] === null)
                            <span class="{{ $kelasDasar }} text-on-surface-muted {{ $item['label'] === '...' ? '' : 'opacity-50' }}">{{ $teks }}</span>
                        @elseif ($item['active'])
                            <span class="{{ $kelasDasar }} bg-primary-soft text-primary" aria-current="page">{{ $teks }}</span>
                        @else
                            <a href="{{ $item['url'] }}" class="{{ $kelasDasar }} border border-control-border bg-surface text-on-surface hover:bg-canvas">{{ $teks }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </nav>
@endif
