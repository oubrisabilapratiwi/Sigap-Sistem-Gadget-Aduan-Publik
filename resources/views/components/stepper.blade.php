@props(['status', 'vertical' => false])
@php
    $flow = \App\Enums\ReportStatus::flow();
    $current = $status->step();
    $rejected = $status->value === 'rejected';
@endphp

@if ($rejected)
    <div class="flex items-center gap-3 rounded-2xl bg-rose-50 p-4 ring-1 ring-rose-200">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500 text-white"><i data-lucide="x" class="h-5 w-5"></i></span>
        <div>
            <p class="text-sm font-extrabold text-rose-800">Laporan ditolak</p>
            <p class="text-xs text-rose-700/80">Tidak memenuhi syarat atau terindikasi spam.</p>
        </div>
    </div>
@else
    <ol class="{{ $vertical ? 'space-y-0' : 'grid grid-cols-4 gap-2' }}">
        @foreach ($flow as $i => $step)
            @php
                $done = $i < $current;
                $now = $i === $current;
                $reached = $i <= $current;
            @endphp
            @if ($vertical)
                <li class="relative flex gap-3 pb-6 last:pb-0">
                    @if (! $loop->last)
                        <span class="absolute left-[17px] top-9 h-[calc(100%-2.25rem)] w-0.5 {{ $done ? 'bg-brand-500' : 'bg-slate-200' }}"></span>
                    @endif
                    <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full ring-4 ring-white
                                 {{ $reached ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400' }} {{ $now ? 'shadow-lg shadow-brand-600/40' : '' }}">
                        <i data-lucide="{{ $done ? 'check' : $step->icon() }}" class="h-4 w-4"></i>
                    </span>
                    <div class="pt-1">
                        <p class="text-sm font-extrabold {{ $reached ? 'text-ink' : 'text-slate-400' }}">{{ $step->label() }}</p>
                        <p class="text-xs {{ $now ? 'text-brand-700 font-semibold' : 'text-slate-400' }}">{{ $now ? 'Tahap saat ini' : $step->description() }}</p>
                    </div>
                </li>
            @else
                <li>
                    <div class="h-1.5 rounded-full {{ $reached ? 'bg-brand-500' : 'bg-slate-200' }} {{ $now ? 'animate-pulse' : '' }}"></div>
                    <p class="mt-2 text-[11px] font-bold leading-tight sm:text-xs {{ $reached ? 'text-ink' : 'text-slate-400' }}">{{ $step->label() }}</p>
                </li>
            @endif
        @endforeach
    </ol>
@endif
