@extends('layouts.app')

@section('title', $report->title . ' — SIGAP')

@section('content')
@php
    $cat = $report->category;
    $proof = $report->proof_photo_url;
    $isOwner = auth()->check() && auth()->id() === $report->user_id;
    $mapMarkers = [[
        'lat' => $report->latitude, 'lng' => $report->longitude, 'title' => $report->title,
        'status' => $report->status->label(), 'color' => $report->status->hex(),
        'votes' => $report->upvotes_count, 'url' => route('reports.show', $report),
    ]];
@endphp

<div class="container-x py-8 sm:py-10">
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300"></i>
        <a href="{{ route('reports.index') }}" class="hover:text-brand-700">Laporan Warga</a>
        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300"></i>
        <span class="truncate font-semibold text-slate-700">{{ $report->tracking_code }}</span>
    </nav>

    <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_380px]">

        {{-- ============ Kolom utama ============ --}}
        <div class="min-w-0 space-y-8">
            <header>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold" style="background: {{ $cat->color }}1a; color: {{ $cat->color }}">
                        <i data-lucide="{{ $cat->icon }}" class="h-3.5 w-3.5"></i>{{ $cat->name }}
                    </span>
                    <x-status-badge :status="$report->status" />
                    <x-priority-badge :report="$report" />
                    <button type="button" x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText('{{ $report->tracking_code }}'); copied = true; setTimeout(() => copied = false, 1800)"
                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 transition hover:bg-slate-200" title="Salin kode pelacakan">
                        <i data-lucide="hash" class="h-3.5 w-3.5"></i><span>{{ $report->tracking_code }}</span>
                        <span x-show="copied" x-cloak class="text-emerald-600">· tersalin</span>
                    </button>
                </div>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-ink sm:text-4xl">{{ $report->title }}</h1>
                <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="user" class="h-4 w-4"></i> {{ $report->user->public_name }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="h-4 w-4"></i> {{ $report->created_at->translatedFormat('d F Y, H:i') }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="h-4 w-4"></i> {{ $report->region->name }}</span>
                </p>
            </header>

            {{-- Foto --}}
            @if ($report->photo)
                <div class="grid gap-4 {{ $proof ? 'sm:grid-cols-2' : '' }}">
                    <figure class="relative overflow-hidden rounded-3xl bg-slate-200 shadow-soft ring-1 ring-slate-200">
                        <img src="{{ $report->photo_url }}" alt="{{ $report->title }}" onclick="openLightbox('{{ $report->photo_url }}')"
                             class="aspect-[4/3] w-full cursor-zoom-in object-cover">
                        <figcaption class="absolute left-3 top-3 rounded-full bg-ink/80 px-3 py-1 text-xs font-bold text-white backdrop-blur">{{ $proof ? 'Sebelum' : 'Foto laporan' }}</figcaption>
                    </figure>
                    @if ($proof)
                        <figure class="relative overflow-hidden rounded-3xl bg-slate-200 shadow-soft ring-1 ring-emerald-300">
                            <img src="{{ $proof }}" alt="Bukti perbaikan" onclick="openLightbox('{{ $proof }}')"
                                 class="aspect-[4/3] w-full cursor-zoom-in object-cover">
                            <figcaption class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white"><i data-lucide="badge-check" class="h-3.5 w-3.5"></i> Sesudah (bukti petugas)</figcaption>
                        </figure>
                    @endif
                </div>
            @endif

            {{-- Deskripsi --}}
            <section class="card p-6 sm:p-8">
                <h2 class="text-lg font-extrabold text-ink">Deskripsi</h2>
                <p class="mt-3 whitespace-pre-line leading-relaxed text-slate-600">{{ $report->description }}</p>

                @if ($report->status->value === 'rejected' && $report->rejection_reason)
                    <div class="mt-5 rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200">
                        <b>Alasan penolakan:</b> {{ $report->rejection_reason }}
                        @if ($report->rejection_photo_url)
                            <button type="button" onclick="openLightbox('{{ $report->rejection_photo_url }}')" class="mt-3 block overflow-hidden rounded-xl ring-1 ring-rose-200">
                                <img src="{{ $report->rejection_photo_url }}" alt="Foto bukti kondisi asli di lokasi" loading="lazy" class="h-36 w-56 cursor-zoom-in object-cover">
                            </button>
                            <p class="mt-1.5 text-xs text-rose-700/80">Foto bukti kondisi asli di lokasi dari petugas.</p>
                        @endif
                    </div>
                @endif
            </section>

            {{-- Lokasi --}}
            <section class="card overflow-hidden p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-extrabold text-ink">Lokasi</h2>
                        <p class="mt-1.5 flex items-start gap-1.5 text-slate-600"><i data-lucide="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600"></i>{{ $report->address }}</p>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}" target="_blank" rel="noopener" class="btn-outline">
                        <i data-lucide="navigation" class="h-4 w-4"></i> Buka di Maps
                    </a>
                </div>
                <div id="detail-map" class="mt-5 h-72 w-full rounded-2xl"></div>
            </section>

            {{-- Timeline --}}
            <section class="card p-6 sm:p-8">
                <h2 class="text-lg font-extrabold text-ink">Riwayat penanganan</h2>
                <p class="mb-6 mt-1 text-sm text-slate-500">Setiap perubahan status tercatat dan tidak dapat dihapus.</p>
                <x-timeline :updates="$report->updates" />
            </section>
        </div>

        {{-- ============ Sidebar ============ --}}
        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <div class="card p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dukungan warga</p>
                <div class="mt-2 flex items-end justify-between gap-4">
                    <p class="text-5xl font-extrabold tracking-tight text-ink"><span data-vote-count="{{ $report->id }}">{{ $report->upvotes_count }}</span></p>
                    <x-vote-button :report="$report" large />
                </div>
                <div class="mt-3"><x-priority-badge :report="$report" /></div>
                <p class="mt-3 text-sm text-slate-500">
                    @if ($report->status->isFinal())
                        Dukungan ditutup karena laporan sudah {{ strtolower($report->status->label()) }}.
                    @elseif ($isOwner)
                        Ini laporan Anda. Bagikan agar tetangga ikut mendukung.
                    @else
                        Dukung jika masalah ini mendesak agar naik ke prioritas teratas.
                    @endif
                </p>
            </div>

            <div class="card p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">Progres</h3>
                <div class="mt-5">
                    <x-stepper :status="$report->status" vertical />
                </div>
            </div>

            <div class="card divide-y divide-slate-100 text-sm">
                <div class="flex items-center justify-between gap-4 px-6 py-3.5"><span class="text-slate-500">Kategori</span><span class="font-bold text-ink">{{ $cat->name }}</span></div>
                @if ($cat->agency)
                    <div class="flex items-center justify-between gap-4 px-6 py-3.5"><span class="text-slate-500">Instansi penanganan</span><span class="text-right font-bold text-ink">{{ $cat->agency->label() }}</span></div>
                @endif
                <div class="flex items-center justify-between gap-4 px-6 py-3.5"><span class="text-slate-500">Wilayah</span><span class="font-bold text-ink">{{ $report->region->name }}</span></div>
                <div class="flex items-center justify-between gap-4 px-6 py-3.5">
                    <span class="text-slate-500">Petugas</span>
                    <span class="text-right font-bold text-ink">
                        {{ $report->assignee?->name ?? 'Belum ditugaskan' }}
                        @if ($report->assignee?->agency_label)<span class="block text-xs font-semibold text-slate-500">{{ $report->assignee->agency_label }}</span>@endif
                    </span>
                </div>
                @if ($report->completed_at)
                    <div class="flex items-center justify-between gap-4 px-6 py-3.5"><span class="text-slate-500">Selesai pada</span><span class="font-bold text-emerald-700">{{ $report->completed_at->translatedFormat('d M Y') }}</span></div>
                @endif
            </div>

            @if ($isOwner && $report->status->value === 'submitted' && ! $report->assigned_to)
                <form method="POST" action="{{ route('reports.destroy', $report) }}"
                      data-confirm="Laporan beserta fotonya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                      data-confirm-title="Hapus laporan ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger" data-confirm-icon="delete">
                    @csrf @method('DELETE')
                    <button class="btn-outline w-full text-rose-600 hover:bg-rose-50"><i data-lucide="trash-2" class="h-4 w-4"></i> Hapus laporan</button>
                </form>
            @endif
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-16">
            <h2 class="text-2xl font-extrabold tracking-tight text-ink">Laporan lain di {{ $report->region->name }}</h2>
            <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $r)
                    <x-report-card :report="$r" />
                @endforeach
            </div>
        </section>
    @endif
</div>

@push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
        const pos = [{{ $report->latitude }}, {{ $report->longitude }}];
        const map = L.map('detail-map', { scrollWheelZoom: false }).setView(pos, 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        L.circleMarker(pos, { radius: 14, color: '#fff', weight: 3, fillColor: @json($report->status->hex()), fillOpacity: .95 }).addTo(map);
        L.circle(pos, { radius: 60, color: @json($report->status->hex()), weight: 1, fillOpacity: .08 }).addTo(map);
        map.on('click', () => map.scrollWheelZoom.enable());
    })();
</script>
@endpush
@endsection
