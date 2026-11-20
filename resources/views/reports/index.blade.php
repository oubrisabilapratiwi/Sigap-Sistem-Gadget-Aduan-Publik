@extends('layouts.app')

@section('title', 'Laporan Warga — SIGAP')

@section('content')
@php
    $hasFilter = collect($filters)->filter()->except('sort')->isNotEmpty();
    $sort = $filters['sort'] ?? 'populer';
@endphp

<section class="border-b border-slate-200 bg-white">
    <div class="container-x py-10 sm:py-12">
        <span class="text-sm font-extrabold uppercase tracking-widest text-brand-600">Transparansi publik</span>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Laporan Warga</h1>
        <p class="mt-2 max-w-2xl text-slate-500">Semua laporan dapat dipantau terbuka. Dukung laporan yang menurutmu mendesak — urutan teratas ditangani lebih dulu.</p>

        {{-- Filter --}}
        <form method="GET" action="{{ route('reports.index') }}" class="mt-8 grid gap-3 md:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr_auto]">
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-400"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul, alamat, atau kode…" class="input pl-11">
            </div>
            <select name="category" class="input" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->slug }}" @selected(($filters['category'] ?? '') === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="region" class="input" onchange="this.form.submit()">
                <option value="">Semua wilayah</option>
                @foreach ($regions as $r)
                    <option value="{{ $r->id }}" @selected(($filters['region'] ?? '') == $r->id)>{{ $r->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="input" onchange="this.form.submit()">
                <option value="populer" @selected($sort === 'populer')>Dukungan terbanyak</option>
                <option value="terbaru" @selected($sort === 'terbaru')>Terbaru</option>
                <option value="terlama" @selected($sort === 'terlama')>Terlama</option>
            </select>
            <button class="btn-primary" type="submit"><i data-lucide="filter" class="h-4 w-4"></i> Terapkan</button>
            @if (!empty($filters['status']))<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
        </form>

        {{-- Tab status --}}
        <div class="mt-5 flex flex-wrap items-center gap-2">
            @php $statusQuery = fn ($s) => route('reports.index', array_filter(array_merge($filters, ['status' => $s, 'page' => null]))); @endphp
            <a href="{{ $statusQuery(null) }}" class="rounded-full px-4 py-1.5 text-sm font-bold transition {{ empty($filters['status']) ? 'bg-ink text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
            @foreach ($statuses as $s)
                <a href="{{ $statusQuery($s->value) }}" class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold transition {{ ($filters['status'] ?? '') === $s->value ? 'bg-ink text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <span class="h-2 w-2 rounded-full {{ $s->dot() }}"></span>{{ $s->label() }}
                </a>
            @endforeach
            @if ($hasFilter)
                <a href="{{ route('reports.index') }}" class="ml-1 inline-flex items-center gap-1 text-sm font-semibold text-rose-600 hover:underline"><i data-lucide="x" class="h-4 w-4"></i> Reset filter</a>
            @endif
        </div>
    </div>
</section>

<section class="container-x py-10" x-data="{ view: 'list' }">
    <div class="mb-6 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-500"><b class="text-ink">{{ $reports->total() }}</b> laporan ditemukan</p>
        <div class="inline-flex rounded-xl bg-slate-200/70 p-1">
            <button @click="view = 'list'" :class="view === 'list' ? 'bg-white text-ink shadow-sm' : 'text-slate-500'" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-bold transition"><i data-lucide="layout-grid" class="h-4 w-4"></i> Daftar</button>
            <button @click="view = 'map'; $nextTick(() => window.showSigapMap && window.showSigapMap('list-map'))" :class="view === 'map' ? 'bg-white text-ink shadow-sm' : 'text-slate-500'" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-bold transition"><i data-lucide="map" class="h-4 w-4"></i> Peta</button>
        </div>
    </div>

    <div x-show="view === 'map'" x-cloak class="card mb-8 overflow-hidden p-2">
        <div id="list-map" class="h-[460px] w-full rounded-xl"></div>
    </div>

    <div x-show="view === 'list'">
        @if ($reports->isEmpty())
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i data-lucide="search-x" class="h-8 w-8"></i></span>
                <h3 class="mt-5 text-xl font-extrabold text-ink">Belum ada laporan yang cocok</h3>
                <p class="mt-2 max-w-sm text-slate-500">Coba ubah kata kunci atau filter, atau jadilah yang pertama melaporkannya.</p>
                @can('report.create')
                    <a href="{{ route('reports.create') }}" class="btn-primary mt-6"><i data-lucide="plus" class="h-4 w-4"></i> Buat Laporan</a>
                @endcan
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($reports as $report)
                    <x-report-card :report="$report" />
                @endforeach
            </div>
            <div class="mt-10">{{ $reports->links() }}</div>
        @endif
    </div>
</section>

@include('partials.map-markers', ['mapId' => 'list-map', 'markers' => $markers])
@endsection
