@props(['report', 'large' => false])
@php
    $voted = (bool) ($report->voted_by_me ?? false);
    $size = $large
        ? 'px-5 py-3 text-base gap-2.5'
        : 'px-3.5 py-2 text-sm gap-2';
    $base = "inline-flex items-center justify-center rounded-xl font-bold ring-1 transition $size";
    $closed = $report->status->isFinal();
    $own = auth()->check() && auth()->id() === $report->user_id;
@endphp

@if ($closed || $own)
    {{-- Dukungan ditutup / laporan sendiri: hanya tampilkan jumlah --}}
    <span class="{{ $base }} bg-slate-50 text-slate-500 ring-slate-200" title="{{ $own ? 'Laporan Anda' : 'Dukungan ditutup' }}">
        <i data-lucide="arrow-big-up" class="{{ $large ? 'h-5 w-5' : 'h-4 w-4' }}"></i>
        <span data-vote-count="{{ $report->id }}">{{ $report->upvotes_count }}</span>
        <span class="font-semibold">dukungan</span>
    </span>
@elseif (auth()->check() && auth()->user()->can('report.vote'))
    <form method="POST" action="{{ route('reports.vote', $report) }}" data-vote="{{ $report->id }}">
        @csrf
        <button type="submit" data-voted="{{ $voted ? 'true' : 'false' }}"
                class="{{ $base }} bg-white text-slate-700 ring-slate-300 hover:bg-brand-50 hover:text-brand-700 hover:ring-brand-300
                       data-[voted=true]:bg-brand-600 data-[voted=true]:text-white data-[voted=true]:ring-brand-600 data-[voted=true]:hover:bg-brand-700">
            <i data-lucide="arrow-big-up" class="{{ $large ? 'h-5 w-5' : 'h-4 w-4' }}"></i>
            <span data-vote-count="{{ $report->id }}">{{ $report->upvotes_count }}</span>
            <span data-vote-label="{{ $report->id }}" class="font-semibold">{{ $voted ? 'Didukung' : 'Dukung' }}</span>
        </button>
    </form>
@elseif (auth()->check())
    <span class="{{ $base }} bg-slate-50 text-slate-500 ring-slate-200">
        <i data-lucide="arrow-big-up" class="{{ $large ? 'h-5 w-5' : 'h-4 w-4' }}"></i> {{ $report->upvotes_count }} dukungan
    </span>
@else
    <a href="{{ route('login') }}" title="Masuk untuk mendukung laporan ini"
       class="{{ $base }} bg-white text-slate-700 ring-slate-300 hover:bg-brand-50 hover:text-brand-700 hover:ring-brand-300">
        <i data-lucide="arrow-big-up" class="{{ $large ? 'h-5 w-5' : 'h-4 w-4' }}"></i>
        {{ $report->upvotes_count }} <span class="font-semibold">Dukung</span>
    </a>
@endif
