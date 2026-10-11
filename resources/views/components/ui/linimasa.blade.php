@props(['log'])
{{-- x-ui.timeline arah B (DESIGN.md 6.12): garis 2px outline-variant, titik 12px (langkah lampau putih berbatas
     outline-variant, langkah sekarang terisi primary), judul langkah sekarang lebih tebal (label-l; token tipografi
     tidak punya body berbobot 700, lihat Do's and Don'ts bobot hanya dari gaya). Kolom waktu (format dan lebar 56px)
     menunggu perubahan format jam lintas halaman (docs/RETEMA_B.md bagian 3), belum diubah di R2. --}}
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
                            <span class="absolute -bottom-5 top-2 w-0.5 bg-outline-variant"></span>
                        @endunless
                        <span class="relative mt-1.5 size-3 rounded-full {{ $terakhir ? 'bg-primary' : 'border-2 border-outline-variant bg-surface-container-lowest' }}"></span>
                    </span>
                    <div class="min-w-0 flex-1 {{ $terakhir ? 'text-label-l' : 'text-body-m' }}">
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
