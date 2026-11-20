@props(['report'])
@php
    $cat = $report->category;
    $color = $cat->color ?? '#2563eb';
@endphp
<article class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-lift">
    <a href="{{ route('reports.show', $report) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
        @if ($report->photo)
            <img src="{{ $report->thumb_url }}" alt="{{ $report->title }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center" style="background: linear-gradient(135deg, {{ $color }}1f, {{ $color }}52)">
                <i data-lucide="{{ $cat->icon }}" class="h-12 w-12" style="color: {{ $color }}"></i>
            </div>
        @endif
        <span class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/30 to-transparent"></span>
        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold shadow-sm backdrop-blur" style="color: {{ $color }}">
            <i data-lucide="{{ $cat->icon }}" class="h-3.5 w-3.5"></i>{{ $cat->name }}
        </span>
        <x-status-badge :status="$report->status" class="absolute right-3 top-3 bg-white/95 shadow-sm backdrop-blur" />
    </a>

    <div class="flex flex-1 flex-col p-5">
        <a href="{{ route('reports.show', $report) }}">
            <h3 class="line-clamp-2 text-[17px] font-extrabold leading-snug tracking-tight text-ink transition group-hover:text-brand-700">{{ $report->title }}</h3>
        </a>

        <p class="mt-2.5 flex items-start gap-1.5 text-sm text-slate-500">
            <i data-lucide="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"></i>
            <span class="line-clamp-1">{{ $report->address }}</span>
        </p>
        <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400">
            <i data-lucide="clock" class="h-3.5 w-3.5"></i>
            {{ $report->region->name }} &middot; {{ $report->created_at->diffForHumans() }}
        </p>

        <div class="mt-auto flex items-center justify-between pt-5">
            <x-vote-button :report="$report" />
            <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center gap-1 text-sm font-bold text-brand-700 transition hover:gap-2">
                Detail <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
        </div>
    </div>
</article>
