@extends('layouts.panel')

@section('title', 'Dashboard Admin — SIGAP')
@section('heading', 'Dashboard')
@section('subheading', 'Ringkasan laporan warga dan beban kerja petugas')

@section('actions')
    <a href="{{ route('admin.reports.index', ['status' => 'submitted']) }}" class="btn-primary hidden sm:inline-flex">
        <i data-lucide="inbox" class="h-4 w-4"></i> Validasi laporan
        @if ($counts['submitted'])<span class="rounded-full bg-white/20 px-2 text-xs">{{ $counts['submitted'] }}</span>@endif
    </a>
@endsection

@section('content')
@php
    $cards = [
        ['submitted', 'Menunggu validasi', 'send', 'from-amber-400 to-orange-500'],
        ['verified', 'Diverifikasi', 'shield-check', 'from-sky-400 to-sky-600'],
        ['in_progress', 'Sedang dikerjakan', 'hammer', 'from-brand-400 to-brand-700'],
        ['done', 'Selesai', 'badge-check', 'from-emerald-400 to-emerald-600'],
    ];
@endphp

<div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach ($cards as [$key, $label, $icon, $grad])
        <a href="{{ route('admin.reports.index', ['status' => $key]) }}" class="card group relative overflow-hidden p-5 transition hover:-translate-y-0.5 hover:shadow-lift sm:p-6">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-white shadow-lg"><i data-lucide="{{ $icon }}" class="h-5 w-5"></i></span>
            <p class="mt-4 text-4xl font-extrabold tracking-tight text-ink">{{ $counts[$key] }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-500">{{ $label }}</p>
            <i data-lucide="arrow-up-right" class="absolute right-5 top-5 h-5 w-5 text-slate-300 transition group-hover:text-brand-600"></i>
        </a>
    @endforeach
</div>

{{-- Distribusi status --}}
<div class="card mt-6 p-6">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-extrabold text-ink">Distribusi status</h2>
        <p class="text-sm text-slate-500">{{ $total }} laporan total</p>
    </div>
    <div class="mt-4 flex h-3.5 overflow-hidden rounded-full bg-slate-100">
        @foreach (\App\Enums\ReportStatus::cases() as $s)
            @if ($total && $counts[$s->value])
                <div class="{{ $s->dot() }} transition-all" style="width: {{ $counts[$s->value] / $total * 100 }}%" title="{{ $s->label() }}: {{ $counts[$s->value] }}"></div>
            @endif
        @endforeach
    </div>
    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
        @foreach (\App\Enums\ReportStatus::cases() as $s)
            <span class="inline-flex items-center gap-2 text-sm text-slate-600"><span class="h-2.5 w-2.5 rounded-full {{ $s->dot() }}"></span>{{ $s->label() }} <b class="text-ink">{{ $counts[$s->value] }}</b></span>
        @endforeach
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_1fr]">

    {{-- Antrean validasi --}}
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="font-extrabold text-ink">Antrean validasi</h2>
                <p class="text-sm text-slate-500">Diurutkan dari dukungan warga terbanyak</p>
            </div>
            <a href="{{ route('admin.reports.index', ['status' => 'submitted']) }}" class="text-sm font-bold text-brand-700 hover:underline">Lihat semua</a>
        </div>

        @forelse ($queue as $report)
            <a href="{{ route('admin.reports.show', $report) }}" class="group flex items-center gap-4 border-b border-slate-100 px-6 py-4 transition last:border-0 hover:bg-slate-50">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background: {{ $report->category->color }}1f; color: {{ $report->category->color }}"><i data-lucide="{{ $report->category->icon }}" class="h-5 w-5"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold text-ink group-hover:text-brand-700">{{ $report->title }}</p>
                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $report->region->name }} · oleh {{ $report->user->name }} · {{ $report->created_at->diffForHumans() }}</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-sm font-extrabold text-slate-700"><i data-lucide="arrow-big-up" class="h-4 w-4 text-brand-600"></i>{{ $report->upvotes_count }}</span>
                <i data-lucide="chevron-right" class="h-5 w-5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600"></i>
            </a>
        @empty
            <div class="flex flex-col items-center px-6 py-16 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><i data-lucide="check-check" class="h-7 w-7"></i></span>
                <p class="mt-4 font-extrabold text-ink">Semua laporan sudah divalidasi</p>
                <p class="mt-1 text-sm text-slate-500">Tidak ada antrean saat ini.</p>
            </div>
        @endforelse
    </section>

    <div class="space-y-6">
        {{-- Kategori --}}
        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Laporan per kategori</h2>
            @php $maxCat = max(1, $categories->max('reports_count')); @endphp
            <div class="mt-5 space-y-4">
                @foreach ($categories as $c)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 font-semibold text-slate-700"><i data-lucide="{{ $c->icon }}" class="h-4 w-4" style="color: {{ $c->color }}"></i>{{ $c->name }}</span>
                            <b class="text-ink">{{ $c->reports_count }}</b>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full" style="width: {{ $c->reports_count / $maxCat * 100 }}%; background: {{ $c->color }}"></div></div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Beban petugas --}}
        <section class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-extrabold text-ink">Beban petugas</h2>
                <a href="{{ route('admin.officers.index') }}" class="text-sm font-bold text-brand-700 hover:underline">Kelola</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($officers as $o)
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink text-xs font-bold text-white">{{ $o->initials }}</span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-sm font-bold text-ink">{{ $o->name }}</p>
                            <p class="text-xs text-slate-500">{{ $o->done_count }} selesai</p>
                        </div>
                        <span class="chip bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">{{ $o->active_count }} aktif</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada petugas.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
