@props(['log'])
@if ($log->isEmpty())
    <p class="text-body-m text-on-surface-variant">Belum ada riwayat.</p>
@else
    <ol {{ $attributes->merge(['class' => 'space-y-0']) }}>
        @foreach ($log as $langkah)
            @php
                $terakhir = $loop->last;
                $status = $langkah->ke_status;
            @endphp
            <li class="relative flex flex-col pb-5 last:pb-0 sm:flex-row sm:gap-3">
                <x-ui.waktu :nilai="$langkah->created_at" class="pl-6 text-body-s text-on-surface-variant sm:w-36 sm:shrink-0 sm:pl-0" />
                <div class="flex min-w-0 flex-1 gap-3">
                    <span class="relative flex w-3 shrink-0 justify-center" aria-hidden="true">
                        @unless ($terakhir)
                            <span class="absolute -bottom-5 top-2 w-px bg-outline-variant"></span>
                        @endunless
                        <span class="relative mt-1.5 size-2.5 rounded-full {{ \App\Support\Tampilan::kelasLatar($status->nada()) }}"></span>
                    </span>
                    <div class="min-w-0 flex-1 text-body-m {{ $terakhir ? 'font-medium' : '' }}">
                        <p>{{ $status->label() }}</p>
                        @if ($langkah->pelaku || $langkah->peran)
                            <p class="text-body-s font-normal text-on-surface-variant">{{ $langkah->pelaku?->name }}@if ($langkah->peran) ({{ $langkah->peran->label() }})@endif</p>
                        @endif
                        @if ($langkah->catatan)
                            <p class="mt-0.5 font-normal text-on-surface">{{ $langkah->catatan }}</p>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
@endif
