@extends('layouts.panel')

@section('title', $report->tracking_code . ' — SIGAP Admin')
@section('heading', 'Tinjau Laporan')
@section('subheading', $report->tracking_code)

@section('actions')
    <a href="{{ route('admin.reports.index') }}" class="btn-outline"><i data-lucide="arrow-left" class="h-4 w-4"></i> <span class="hidden sm:inline">Kembali</span></a>
@endsection

@push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush

@section('content')
@php
    $cat = $report->category;
    $status = $report->status->value;
    $proof = $report->proof_photo_url;
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_400px]">

    {{-- ========== Detail ========== --}}
    <div class="min-w-0 space-y-6">
        <section class="card overflow-hidden">
            @if ($report->photo)
                <div class="grid {{ $proof ? 'sm:grid-cols-2' : '' }}">
                    <div class="relative">
                        <img src="{{ $report->photo_url }}" alt="{{ $report->title }}" onclick="openLightbox('{{ $report->photo_url }}')" class="aspect-[16/10] w-full cursor-zoom-in object-cover">
                        <span class="absolute left-3 top-3 rounded-full bg-ink/80 px-3 py-1 text-xs font-bold text-white">{{ $proof ? 'Sebelum' : 'Foto laporan' }}</span>
                    </div>
                    @if ($proof)
                        <div class="relative">
                            <img src="{{ $proof }}" alt="Bukti" onclick="openLightbox('{{ $proof }}')" class="aspect-[16/10] w-full cursor-zoom-in object-cover">
                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white"><i data-lucide="badge-check" class="h-3.5 w-3.5"></i> Sesudah</span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold" style="background: {{ $cat->color }}1a; color: {{ $cat->color }}"><i data-lucide="{{ $cat->icon }}" class="h-3.5 w-3.5"></i>{{ $cat->name }}</span>
                    <x-status-badge :status="$report->status" />
                    <span class="chip bg-slate-100 text-slate-700"><i data-lucide="arrow-big-up" class="h-3.5 w-3.5"></i>{{ $report->upvotes_count }} dukungan</span>
                </div>
                <h2 class="mt-4 text-2xl font-extrabold leading-snug tracking-tight text-ink">{{ $report->title }}</h2>
                <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-600">{{ $report->description }}</p>

                <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-6 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-400">Pelapor</dt><dd class="mt-0.5 font-bold text-ink">{{ $report->user->name }}</dd><dd class="text-xs text-slate-500">{{ $report->user->email }}{{ $report->user->phone ? ' · ' . $report->user->phone : '' }}</dd></div>
                    <div><dt class="text-slate-400">Dikirim</dt><dd class="mt-0.5 font-bold text-ink">{{ $report->created_at->translatedFormat('d F Y, H:i') }}</dd></div>
                    <div><dt class="text-slate-400">Wilayah</dt><dd class="mt-0.5 font-bold text-ink">{{ $report->region->name }}</dd></div>
                    <div><dt class="text-slate-400">Alamat</dt><dd class="mt-0.5 font-bold text-ink">{{ $report->address }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-extrabold text-ink">Titik lokasi</h2>
                <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}" target="_blank" rel="noopener" class="btn-outline !py-2"><i data-lucide="navigation" class="h-4 w-4"></i> Buka di Maps</a>
            </div>
            <div id="detail-map" class="mt-4 h-64 w-full rounded-2xl"></div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="mb-6 font-extrabold text-ink">Riwayat penanganan</h2>
            <x-timeline :updates="$report->updates" />
        </section>
    </div>

    {{-- ========== Aksi ========== --}}
    <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">

        @if ($status === 'submitted')
            {{-- Verifikasi & disposisi --}}
            <form method="POST" action="{{ route('admin.reports.verify', $report) }}" class="card overflow-hidden">
                @csrf
                <div class="border-b border-emerald-100 bg-emerald-50 px-6 py-4">
                    <h3 class="flex items-center gap-2 font-extrabold text-emerald-900"><i data-lucide="shield-check" class="h-5 w-5"></i> Verifikasi &amp; disposisi</h3>
                    <p class="mt-0.5 text-xs text-emerald-800/80">Laporan valid? Tugaskan ke petugas lapangan.</p>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label class="label">Pilih petugas</label>
                        <div class="space-y-2">
                            @forelse ($officers as $o)
                                <label class="cursor-pointer">
                                    <input type="radio" name="assigned_to" value="{{ $o->id }}" class="peer sr-only" @checked(old('assigned_to') == $o->id) required>
                                    <span class="flex items-center gap-3 rounded-xl border-2 border-slate-200 p-3 transition peer-checked:border-brand-600 peer-checked:bg-brand-50 hover:border-slate-300">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink text-xs font-bold text-white">{{ $o->initials }}</span>
                                        <span class="min-w-0 flex-1 leading-tight">
                                            <span class="block truncate text-sm font-bold text-ink">{{ $o->name }}</span>
                                            <span class="block text-xs text-slate-500">{{ $o->region?->name ?? '—' }} · {{ $o->active_count }} tugas aktif</span>
                                        </span>
                                        @if ($o->region_id === $report->region_id)
                                            <span class="chip bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-200">Satu wilayah</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Belum ada petugas aktif. <a href="{{ route('admin.officers.index') }}" class="font-bold underline">Tambah petugas</a></p>
                            @endforelse
                        </div>
                        @error('assigned_to') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="note" class="label">Catatan untuk petugas <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="note" name="note" rows="2" maxlength="500" class="input" placeholder="Mis. bawa alat penambal aspal">{{ old('note') }}</textarea>
                    </div>
                    <button class="btn-primary w-full" @disabled($officers->isEmpty())><i data-lucide="send" class="h-4 w-4"></i> Verifikasi &amp; tugaskan</button>
                </div>
            </form>

            {{-- Tolak --}}
            <form method="POST" action="{{ route('admin.reports.reject', $report) }}" class="card overflow-hidden" x-data="{ open: {{ $errors->has('rejection_reason') ? 'true' : 'false' }} }"
                  data-confirm="Laporan akan disembunyikan dari publik. Pelapor tetap dapat melihat alasan penolakan di halaman laporannya."
                  data-confirm-title="Tolak laporan ini?" data-confirm-ok="Ya, tolak laporan" data-confirm-tone="danger"
                  data-confirm-icon="reject" data-confirm-detail-from="#rejection_reason">
                @csrf
                <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-6 py-4 text-left">
                    <span class="flex items-center gap-2 font-extrabold text-rose-700"><i data-lucide="shield-x" class="h-5 w-5"></i> Tolak / tandai spam</span>
                    <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" x-cloak x-transition class="space-y-4 border-t border-slate-100 p-6">
                    <div>
                        <label for="rejection_reason" class="label">Alasan penolakan</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" minlength="10" maxlength="500" required class="input @error('rejection_reason') !border-rose-400 @enderror" placeholder="Mis. foto tidak sesuai lokasi / laporan duplikat / terindikasi spam">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-start gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="block_user" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span>Nonaktifkan akun pelapor <span class="text-slate-400">(untuk spam berulang)</span></span>
                    </label>
                    <button class="btn-danger w-full"><i data-lucide="x-circle" class="h-4 w-4"></i> Tolak laporan</button>
                </div>
            </form>

        @elseif (in_array($status, ['verified', 'in_progress']))
            <div class="card p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">Petugas penanggung jawab</h3>
                <div class="mt-4 flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-ink font-bold text-white">{{ $report->assignee?->initials ?? '?' }}</span>
                    <div class="leading-tight">
                        <p class="font-extrabold text-ink">{{ $report->assignee?->name ?? 'Belum ditugaskan' }}</p>
                        <p class="text-sm text-slate-500">{{ $report->assignee?->phone ?? $report->assignee?->email }}</p>
                    </div>
                </div>
                @if ($report->verifier)
                    <p class="mt-4 text-xs text-slate-400">Diverifikasi oleh {{ $report->verifier->name }} · {{ optional($report->verified_at)->translatedFormat('d M Y, H:i') }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.reports.reassign', $report) }}" class="card p-6">
                @csrf
                <h3 class="font-extrabold text-ink">Alihkan tugas</h3>
                <p class="mt-1 text-sm text-slate-500">Pindahkan ke petugas lain bila perlu.</p>
                <select name="assigned_to" class="input mt-4" required>
                    <option value="">Pilih petugas…</option>
                    @foreach ($officers as $o)
                        <option value="{{ $o->id }}" @disabled($o->id === $report->assigned_to)>{{ $o->name }} — {{ $o->region?->name ?? '—' }} ({{ $o->active_count }} aktif){{ $o->id === $report->assigned_to ? ' • saat ini' : '' }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                <button class="btn-outline mt-3 w-full"><i data-lucide="repeat" class="h-4 w-4"></i> Alihkan</button>
            </form>

        @elseif ($status === 'done')
            <div class="card border-emerald-200 bg-emerald-50 p-6 ring-emerald-200">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white"><i data-lucide="badge-check" class="h-6 w-6"></i></span>
                <h3 class="mt-4 font-extrabold text-emerald-900">Laporan selesai</h3>
                <p class="mt-1 text-sm text-emerald-800/80">Dikerjakan oleh {{ $report->assignee?->name ?? '—' }} dan sudah disertai foto bukti.</p>
                @if ($report->completed_at)<p class="mt-3 text-xs text-emerald-800/70">Selesai {{ $report->completed_at->translatedFormat('d F Y, H:i') }}</p>@endif
            </div>

        @else {{-- rejected --}}
            <div class="card border-rose-200 bg-rose-50 p-6 ring-rose-200">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500 text-white"><i data-lucide="x-circle" class="h-6 w-6"></i></span>
                <h3 class="mt-4 font-extrabold text-rose-900">Laporan ditolak</h3>
                <p class="mt-1 text-sm text-rose-800/90">{{ $report->rejection_reason }}</p>
                @if ($report->verifier)<p class="mt-3 text-xs text-rose-800/70">Oleh {{ $report->verifier->name }}</p>@endif
            </div>
        @endif

        <div class="card p-6">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">Progres</h3>
            <div class="mt-5"><x-stepper :status="$report->status" vertical /></div>
        </div>

        <a href="{{ route('reports.show', $report) }}" target="_blank" class="btn-outline w-full"><i data-lucide="external-link" class="h-4 w-4"></i> Lihat tampilan publik</a>
    </aside>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
        const pos = [{{ $report->latitude }}, {{ $report->longitude }}];
        const map = L.map('detail-map', { scrollWheelZoom: false }).setView(pos, 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        L.circleMarker(pos, { radius: 13, color: '#fff', weight: 3, fillColor: @json($report->status->hex()), fillOpacity: .95 }).addTo(map);
        map.on('click', () => map.scrollWheelZoom.enable());
    })();
</script>
@endpush
