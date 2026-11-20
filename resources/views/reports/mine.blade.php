@extends('layouts.app')

@section('title', 'Laporan Saya — SIGAP')

@section('content')
<div class="container-x py-10 sm:py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-sm font-extrabold uppercase tracking-widest text-brand-600">Akun saya</span>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Laporan Saya</h1>
            <p class="mt-2 text-slate-500">Pantau progres setiap laporan yang kamu kirim.</p>
        </div>
        <a href="{{ route('reports.create') }}" class="btn-primary"><i data-lucide="plus" class="h-4 w-4"></i> Buat Laporan</a>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach (\App\Enums\ReportStatus::cases() as $s)
            <div class="card flex items-center gap-4 p-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl text-white {{ $s->dot() }}"><i data-lucide="{{ $s->icon() }}" class="h-5 w-5"></i></span>
                <div class="leading-tight">
                    <p class="text-2xl font-extrabold text-ink">{{ $counts[$s->value] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">{{ $s->label() }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-10">
        @if ($reports->isEmpty())
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="camera" class="h-8 w-8"></i></span>
                <h3 class="mt-5 text-xl font-extrabold text-ink">Kamu belum membuat laporan</h3>
                <p class="mt-2 max-w-sm text-slate-500">Lihat sesuatu yang rusak di sekitarmu? Foto dan laporkan sekarang.</p>
                <a href="{{ route('reports.create') }}" class="btn-primary mt-6"><i data-lucide="plus" class="h-4 w-4"></i> Buat Laporan Pertama</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($reports as $report)
                    <a href="{{ route('reports.show', $report) }}" class="card group flex flex-col gap-4 p-4 transition hover:shadow-lift sm:flex-row sm:items-center sm:p-5">
                        <div class="relative h-40 w-full shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:h-24 sm:w-36">
                            @if ($report->photo)
                                <img src="{{ $report->thumb_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center" style="background: {{ $report->category->color }}22; color: {{ $report->category->color }}"><i data-lucide="{{ $report->category->icon }}" class="h-8 w-8"></i></div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$report->status" />
                                <span class="text-xs font-semibold text-slate-400">{{ $report->tracking_code }}</span>
                            </div>
                            <h3 class="mt-2 truncate text-lg font-extrabold text-ink group-hover:text-brand-700">{{ $report->title }}</h3>
                            <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500"><i data-lucide="map-pin" class="h-4 w-4"></i><span class="truncate">{{ $report->address }}</span></p>
                            @if ($report->status->value === 'rejected' && $report->rejection_reason)
                                <p class="mt-2 text-sm text-rose-600">Ditolak: {{ $report->rejection_reason }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-6 sm:flex-col sm:items-end sm:gap-1">
                            <p class="flex items-center gap-1.5 text-lg font-extrabold text-ink"><i data-lucide="arrow-big-up" class="h-5 w-5 text-brand-600"></i>{{ $report->upvotes_count }}</p>
                            <p class="text-xs text-slate-400">{{ $report->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $reports->links() }}</div>
        @endif
    </div>
</div>
@endsection
