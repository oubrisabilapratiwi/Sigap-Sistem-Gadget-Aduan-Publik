@extends('layouts.app')

@section('title', 'SIGAP — Lapor, Pantau, Selesai')

@section('content')

{{-- ============================== HERO ============================== --}}
<section class="relative overflow-hidden bg-ink text-white">
    <div class="absolute inset-0 opacity-[.08]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 26px 26px"></div>
    <div class="absolute -right-24 -top-40 h-[28rem] w-[28rem] rounded-full bg-brand-500/30 blur-3xl"></div>
    <div class="absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-orange-400/20 blur-3xl"></div>

    <div class="container-x relative grid items-center gap-14 pb-36 pt-16 lg:grid-cols-[1.1fr_.9fr] lg:pb-44 lg:pt-24">
        <div class="animate-rise">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-brand-200 ring-1 ring-white/15">
                <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-orange-400"></span></span>
                Pelaporan infrastruktur berbasis wilayah
            </span>

            <h1 class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-[3.6rem]">
                Jalan berlubang? Lampu mati?
                <span class="bg-gradient-to-r from-brand-300 to-orange-300 bg-clip-text text-transparent">Laporkan, pantau, selesai.</span>
            </h1>

            <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-300">
                Foto, tandai lokasinya di peta, lalu ikuti progres penanganannya secara terbuka —
                dari diverifikasi, dikerjakan, sampai selesai dengan foto bukti dari petugas.
            </p>

            <div class="mt-9 flex flex-wrap items-center gap-3">
                @auth
                    @can('report.create')
                        <a href="{{ route('reports.create') }}" class="btn-accent px-6 py-3.5 text-base"><i data-lucide="camera" class="h-5 w-5"></i> Buat Laporan</a>
                    @else
                        <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn-accent px-6 py-3.5 text-base"><i data-lucide="layout-dashboard" class="h-5 w-5"></i> Buka Panel</a>
                    @endcan
                @else
                    <a href="{{ route('register') }}" class="btn-accent px-6 py-3.5 text-base"><i data-lucide="camera" class="h-5 w-5"></i> Mulai Melapor</a>
                @endauth
                <a href="{{ route('reports.index') }}" class="btn border border-white/20 bg-white/5 px-6 py-3.5 text-base text-white hover:bg-white/10 focus:ring-white/20">
                    <i data-lucide="map" class="h-5 w-5"></i> Lihat Laporan Warga
                </a>
            </div>

            <div class="mt-10 flex items-center gap-4 text-sm text-slate-400">
                <div class="flex -space-x-2">
                    @foreach (['bg-brand-500', 'bg-orange-400', 'bg-sky-500', 'bg-rose-400'] as $c)
                        <span class="h-8 w-8 rounded-full {{ $c }} ring-2 ring-ink"></span>
                    @endforeach
                </div>
                <p><b class="text-white">{{ number_format($stats['total']) }}</b> laporan warga sudah tercatat, <b class="text-white">{{ $stats['rate'] }}%</b> berhasil diselesaikan.</p>
            </div>
        </div>

        {{-- Kartu ilustrasi alur --}}
        <div class="relative hidden animate-rise lg:block" style="animation-delay:.15s">
            <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-brand-500/20 to-orange-400/10 blur-2xl"></div>
            <div class="relative animate-float rounded-3xl bg-white p-5 text-slate-700 shadow-2xl shadow-black/40">
                <a href="{{ $hero['url'] }}" class="relative flex aspect-[16/10] items-center justify-center overflow-hidden rounded-2xl" style="background: linear-gradient(135deg, {{ $hero['color'] }}33, {{ $hero['color'] }}77)">
                    @if ($hero['photo'])
                        <img src="{{ $hero['photo'] }}" alt="{{ $hero['title'] }}" class="absolute inset-0 h-full w-full object-cover">
                    @else
                        <i data-lucide="{{ $hero['icon'] }}" class="h-20 w-20" style="color: {{ $hero['color'] }}"></i>
                    @endif
                    <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-bold shadow-sm" style="color: {{ $hero['color'] }}"><i data-lucide="{{ $hero['icon'] }}" class="h-3.5 w-3.5"></i> {{ $hero['category'] }}</span>
                    @if ($hero['status'])
                        <x-status-badge :status="$hero['status']" class="absolute right-3 top-3 bg-white shadow-sm" />
                    @else
                        <span class="chip absolute right-3 top-3 bg-white text-amber-700 ring-1 ring-inset ring-amber-300/70"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Sedang Dikerjakan</span>
                    @endif
                </a>
                <h3 class="mt-4 line-clamp-2 text-lg font-extrabold leading-snug text-ink">{{ $hero['title'] }}</h3>
                <p class="mt-1.5 flex items-center gap-1.5 text-sm text-slate-500"><i data-lucide="map-pin" class="h-4 w-4 shrink-0"></i> <span class="truncate">{{ $hero['address'] }}</span></p>

                <div class="mt-5 grid grid-cols-4 gap-2">
                    @foreach (['Diajukan', 'Diverifikasi', 'Dikerjakan', 'Selesai'] as $i => $label)
                        <div>
                            <div class="h-1.5 rounded-full {{ $i <= $hero['step'] ? 'bg-brand-500' : 'bg-slate-200' }}"></div>
                            <p class="mt-1.5 text-[11px] font-bold {{ $i <= $hero['step'] ? 'text-ink' : 'text-slate-400' }}">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-3.5 py-2 text-sm font-bold text-white"><i data-lucide="arrow-big-up" class="h-4 w-4"></i> {{ $hero['votes'] }} Didukung</span>
                    <span class="text-xs font-semibold text-slate-400">{{ $hero['region'] }}</span>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ============================== STATISTIK ============================== --}}
<section class="container-x relative z-10 -mt-20">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $items = [
                ['Total Laporan', $stats['total'], 'file-text', 'text-brand-700 bg-brand-50'],
                ['Sedang Diproses', $stats['processing'], 'hammer', 'text-amber-700 bg-amber-50'],
                ['Selesai Diperbaiki', $stats['done'], 'badge-check', 'text-emerald-700 bg-emerald-50'],
                ['Tingkat Penyelesaian', $stats['rate'] . '%', 'trending-up', 'text-sky-700 bg-sky-50'],
            ];
        @endphp
        @foreach ($items as [$label, $value, $icon, $tone])
            <div class="card p-5 sm:p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $tone }}"><i data-lucide="{{ $icon }}" class="h-5 w-5"></i></span>
                <p class="mt-4 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ============================== CARA KERJA ============================== --}}
<section id="cara-kerja" class="container-x scroll-mt-24 pt-24">
    <div class="mx-auto max-w-2xl text-center">
        <span class="text-sm font-extrabold uppercase tracking-widest text-brand-600">Cara kerja</span>
        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Empat langkah, tanpa laporan yang menguap</h2>
        <p class="mt-4 text-slate-500">Setiap laporan punya kode pelacakan dan riwayat status yang bisa dilihat siapa saja.</p>
    </div>

    <div class="relative mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="absolute left-[12%] right-[12%] top-12 hidden h-0.5 bg-gradient-to-r from-brand-200 via-brand-400 to-brand-200 lg:block"></div>
        @foreach ([
            ['1', 'Warga melapor', 'Unggah foto bukti, pilih kategori, dan tandai titik lokasi di peta.', 'camera'],
            ['2', 'Admin memverifikasi', 'Admin menyaring laporan spam/palsu lalu mendisposisikan ke petugas yang tepat.', 'shield-check'],
            ['3', 'Petugas mengerjakan', 'Petugas lapangan memperbarui progres langsung dari lokasi kejadian.', 'hammer'],
            ['4', 'Selesai + bukti', 'Laporan hanya bisa ditutup bila petugas mengunggah foto hasil perbaikan.', 'badge-check'],
        ] as [$n, $title, $text, $icon])
            <div class="card relative p-6 text-center transition hover:-translate-y-1 hover:shadow-lift">
                <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-800 text-white shadow-lg shadow-brand-700/30">
                    <i data-lucide="{{ $icon }}" class="h-6 w-6"></i>
                    <span class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-orange-400 text-xs font-extrabold text-ink ring-2 ring-white">{{ $n }}</span>
                </span>
                <h3 class="mt-5 text-lg font-extrabold text-ink">{{ $title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $text }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 flex flex-col items-center gap-4 rounded-3xl bg-brand-50 p-6 ring-1 ring-brand-100 sm:flex-row sm:p-8">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-700 shadow-sm"><i data-lucide="arrow-big-up" class="h-7 w-7"></i></span>
        <div class="text-center sm:text-left">
            <h3 class="text-lg font-extrabold text-ink">Suara warga menentukan prioritas</h3>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Dukung laporan tetangga yang menurutmu mendesak. Laporan dengan dukungan terbanyak naik ke urutan teratas agar ditangani lebih dulu.</p>
        </div>
    </div>
</section>

{{-- ============================== KATEGORI ============================== --}}
<section class="container-x pt-24">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-sm font-extrabold uppercase tracking-widest text-brand-600">Kategori</span>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink">Apa yang ingin kamu laporkan?</h2>
        </div>
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-700 hover:gap-2.5 transition-all">Semua laporan <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach ($categories as $cat)
            <a href="{{ route('reports.index', ['category' => $cat->slug]) }}"
               class="card group flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition group-hover:scale-110" style="background: {{ $cat->color }}1f; color: {{ $cat->color }}">
                    <i data-lucide="{{ $cat->icon }}" class="h-6 w-6"></i>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-extrabold text-ink">{{ $cat->name }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ $cat->reports_count }} laporan</span>
                </span>
            </a>
        @endforeach
    </div>
</section>

{{-- ============================== PRIORITAS ============================== --}}
<section class="container-x pt-24">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-sm font-extrabold uppercase tracking-widest text-amber-600">Paling mendesak</span>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink">Didukung warga terbanyak</h2>
            <p class="mt-2 text-slate-500">Laporan yang belum selesai, diurutkan dari dukungan tertinggi.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn-outline">Lihat semua <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
    </div>

    @if ($urgent->isEmpty())
        <div class="card mt-8 p-12 text-center text-slate-500">Belum ada laporan aktif. Jadilah yang pertama melapor!</div>
    @else
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($urgent as $report)
                <x-report-card :report="$report" />
            @endforeach
        </div>
    @endif
</section>

{{-- ============================== PETA ============================== --}}
<section class="container-x pt-24">
    <div class="text-center">
        <span class="text-sm font-extrabold uppercase tracking-widest text-brand-600">Peta laporan</span>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink">Sebaran masalah di lingkunganmu</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">Semakin besar lingkaran, semakin banyak dukungan warga. Klik titik untuk melihat detail.</p>
    </div>
    <div class="card mt-8 overflow-hidden p-2">
        <div id="home-map" class="h-[420px] w-full rounded-xl"></div>
    </div>
    <div class="mt-4 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs font-semibold text-slate-500">
        @foreach (\App\Enums\ReportStatus::flow() as $s)
            @if ($s->value !== 'done')
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full {{ $s->dot() }}"></span>{{ $s->label() }}</span>
            @endif
        @endforeach
    </div>
</section>

{{-- ============================== BARU SELESAI ============================== --}}
@if ($recentDone->isNotEmpty())
<section class="container-x pt-24">
    <div class="rounded-3xl bg-gradient-to-br from-emerald-50 to-brand-50 p-6 ring-1 ring-emerald-100 sm:p-10">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500 text-white"><i data-lucide="badge-check" class="h-6 w-6"></i></span>
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-ink">Baru saja diselesaikan</h2>
                <p class="text-sm text-slate-600">Bukti bahwa suara warga didengar.</p>
            </div>
        </div>
        <div class="mt-7 grid gap-4 md:grid-cols-2">
            @foreach ($recentDone as $report)
                <a href="{{ route('reports.show', $report) }}" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-emerald-100 transition hover:shadow-lift">
                    <span class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl" style="background: {{ $report->category->color }}1f; color: {{ $report->category->color }}">
                        @if ($report->photo)
                            <img src="{{ $report->thumb_url }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                        @else
                            <i data-lucide="{{ $report->category->icon }}" class="h-6 w-6"></i>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-extrabold text-ink group-hover:text-brand-700">{{ $report->title }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $report->region->name }} &middot; selesai {{ optional($report->completed_at)->diffForHumans() }}</span>
                    </span>
                    <i data-lucide="chevron-right" class="h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-brand-600"></i>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================== CTA ============================== --}}
<section class="container-x pt-24">
    <div class="relative overflow-hidden rounded-3xl bg-ink px-6 py-14 text-center text-white sm:px-12 sm:py-16">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-500/30 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-10 h-64 w-64 rounded-full bg-orange-400/20 blur-3xl"></div>
        <div class="relative">
            <h2 class="mx-auto max-w-2xl text-3xl font-extrabold tracking-tight sm:text-4xl">Lingkungan yang lebih baik dimulai dari satu laporan.</h2>
            <p class="mx-auto mt-4 max-w-xl text-slate-300">Hanya butuh satu menit: foto, pilih lokasi, kirim.</p>
            <div class="mt-8">
                @auth
                    @can('report.create')
                        <a href="{{ route('reports.create') }}" class="btn-accent px-7 py-3.5 text-base"><i data-lucide="camera" class="h-5 w-5"></i> Buat Laporan Sekarang</a>
                    @endcan
                @else
                    <a href="{{ route('register') }}" class="btn-accent px-7 py-3.5 text-base">Daftar Gratis <i data-lucide="arrow-right" class="h-5 w-5"></i></a>
                @endauth
            </div>
        </div>
    </div>
</section>

@include('partials.map-markers', ['mapId' => 'home-map', 'markers' => $markers])
@endsection
