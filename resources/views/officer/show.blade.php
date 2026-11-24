@extends('layouts.panel')

@section('title', $report->tracking_code . ' — Tugas SIGAP')
@section('heading', 'Detail Tugas')
@section('subheading', $report->tracking_code)

@section('actions')
    <a href="{{ route('officer.tasks.index') }}" class="btn-outline"><i data-lucide="arrow-left" class="h-4 w-4"></i> <span class="hidden sm:inline">Kembali</span></a>
@endsection

@push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush

@section('content')
@php
    $cat = $report->category;
    $status = $report->status->value;
    $proof = $report->proof_photo_url;
    $closed = $report->status->isFinal();
    $isCheck = $status === 'submitted'; // tahap cek lapangan: valid atau hoax
    $defaultStatus = old('status', $isCheck ? 'verified' : ($status === 'verified' ? 'in_progress' : 'done'));
    $rejectionPhoto = $report->rejection_photo_url;
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_420px]">

    <div class="min-w-0 space-y-6">
        <section class="card overflow-hidden">
            @if ($report->photo)
                <div class="grid {{ $proof ? 'sm:grid-cols-2' : '' }}">
                    <div class="relative">
                        <img src="{{ $report->photo_url }}" alt="" onclick="openLightbox('{{ $report->photo_url }}')" class="aspect-[16/10] w-full cursor-zoom-in object-cover">
                        <span class="absolute left-3 top-3 rounded-full bg-ink/80 px-3 py-1 text-xs font-bold text-white">{{ $proof ? 'Sebelum' : 'Foto laporan warga' }}</span>
                    </div>
                    @if ($proof)
                        <div class="relative">
                            <img src="{{ $proof }}" alt="" onclick="openLightbox('{{ $proof }}')" class="aspect-[16/10] w-full cursor-zoom-in object-cover">
                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white"><i data-lucide="badge-check" class="h-3.5 w-3.5"></i> Bukti perbaikan</span>
                        </div>
                    @endif
                </div>
            @endif
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold" style="background: {{ $cat->color }}1a; color: {{ $cat->color }}"><i data-lucide="{{ $cat->icon }}" class="h-3.5 w-3.5"></i>{{ $cat->name }}</span>
                    <x-status-badge :status="$report->status" />
                    <x-priority-badge :report="$report" />
                    <span class="chip bg-slate-100 text-slate-700"><i data-lucide="arrow-big-up" class="h-3.5 w-3.5"></i>{{ $report->upvotes_count }} dukungan</span>
                </div>
                <h2 class="mt-4 text-2xl font-extrabold leading-snug tracking-tight text-ink">{{ $report->title }}</h2>
                <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-600">{{ $report->description }}</p>
                <p class="mt-5 flex items-start gap-2 rounded-xl bg-slate-50 p-3.5 text-sm text-slate-600"><i data-lucide="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600"></i>{{ $report->address }} <span class="text-slate-400">· {{ $report->region->name }}</span></p>
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-extrabold text-ink">Lokasi</h2>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $report->latitude }},{{ $report->longitude }}" target="_blank" rel="noopener" class="btn-primary !py-2"><i data-lucide="navigation" class="h-4 w-4"></i> Rute ke lokasi</a>
            </div>
            <div id="detail-map" class="mt-4 h-64 w-full rounded-2xl"></div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="mb-6 font-extrabold text-ink">Riwayat penanganan</h2>
            <x-timeline :updates="$report->updates" />
        </section>
    </div>

    {{-- ========== Form update ========== --}}
    <aside class="xl:sticky xl:top-24 xl:self-start">
        @if ($closed && $status === 'rejected')
            <div class="card border-rose-200 bg-rose-50 p-6 ring-rose-200">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500 text-white"><i data-lucide="x-circle" class="h-6 w-6"></i></span>
                <h3 class="mt-4 font-extrabold text-rose-900">Laporan ditolak</h3>
                <p class="mt-1 text-sm text-rose-800/90">{{ $report->rejection_reason }}</p>
                @if ($rejectionPhoto)
                    <button type="button" onclick="openLightbox('{{ $rejectionPhoto }}')" class="mt-4 block overflow-hidden rounded-xl ring-1 ring-rose-200">
                        <img src="{{ $rejectionPhoto }}" alt="Foto bukti kondisi asli di lokasi" loading="lazy" class="aspect-[16/10] w-full cursor-zoom-in object-cover">
                    </button>
                    <p class="mt-1.5 text-xs text-rose-800/70">Foto bukti kondisi asli di lokasi.</p>
                @endif
            </div>
        @elseif ($closed)
            <div class="card border-emerald-200 bg-emerald-50 p-6 ring-emerald-200">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white"><i data-lucide="badge-check" class="h-6 w-6"></i></span>
                <h3 class="mt-4 font-extrabold text-emerald-900">Tugas selesai</h3>
                <p class="mt-1 text-sm text-emerald-800/80">Laporan ini sudah ditutup dengan foto bukti. Terima kasih!</p>
                @if ($report->completed_at)<p class="mt-3 text-xs text-emerald-800/70">{{ $report->completed_at->translatedFormat('d F Y, H:i') }}</p>@endif
            </div>
        @else
            <form method="POST" action="{{ route('officer.tasks.update', $report) }}" enctype="multipart/form-data" class="card overflow-hidden"
                  x-data="{ status: '{{ $defaultStatus }}', preview: null, name: '', sending: false }" @submit="sending = true">
                @csrf
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <h3 class="font-extrabold text-ink">{{ $isCheck ? 'Cek kebenaran laporan' : 'Perbarui progres' }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Status saat ini: <b>{{ $report->status->label() }}</b></p>
                </div>

                <div class="space-y-5 p-6">
                    @if ($isCheck)
                    <div>
                        <label class="label">Hasil pengecekan di lokasi</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="verified" x-model="status" class="peer sr-only">
                                <span class="flex flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-sky-500 peer-checked:bg-sky-50 hover:border-slate-300">
                                    <i data-lucide="shield-check" class="h-6 w-6 text-sky-600"></i><span class="text-sm font-bold text-ink">Laporan benar</span>
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="rejected" x-model="status" class="peer sr-only">
                                <span class="flex flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-rose-500 peer-checked:bg-rose-50 hover:border-slate-300">
                                    <i data-lucide="shield-x" class="h-6 w-6 text-rose-600"></i><span class="text-sm font-bold text-ink">Hoax / bohong</span>
                                </span>
                            </label>
                        </div>
                        @error('status') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    @else
                    <div>
                        <label class="label">Status pengerjaan</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="in_progress" x-model="status" class="peer sr-only">
                                <span class="flex flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-slate-300">
                                    <i data-lucide="hammer" class="h-6 w-6 text-amber-600"></i><span class="text-sm font-bold text-ink">Sedang dikerjakan</span>
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="done" x-model="status" class="peer sr-only">
                                <span class="flex flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-slate-300">
                                    <i data-lucide="badge-check" class="h-6 w-6 text-emerald-600"></i><span class="text-sm font-bold text-ink">Selesai</span>
                                </span>
                            </label>
                        </div>
                        @error('status') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    <div>
                        <label for="note" class="label">
                            <span x-text="status === 'rejected' ? 'Alasan penolakan / temuan di lapangan' : (status === 'verified' ? 'Catatan pengecekan' : 'Catatan progres')">Catatan progres</span>
                            <span x-show="status === 'done' || status === 'verified'" class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <textarea id="note" name="note" rows="3" maxlength="1000" class="input @error('note') !border-rose-400 @enderror" x-bind:placeholder="status === 'rejected' ? 'Mis. lokasi tidak ditemukan kerusakan, foto pelapor bukan dari lokasi ini' : (status === 'verified' ? 'Mis. kerusakan sesuai laporan, perlu alat berat' : 'Apa yang sudah dikerjakan?')" placeholder="Apa yang sudah dikerjakan?">{{ old('note') }}</textarea>
                        @error('note') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="status === 'done' || status === 'rejected'" x-cloak x-transition>
                        <label class="label"><span x-text="status === 'rejected' ? 'Foto bukti kondisi asli di lokasi' : 'Foto bukti perbaikan'">Foto bukti perbaikan</span> <span class="text-rose-600">*wajib</span></label>
                        <label for="photo" class="relative flex min-h-[160px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed bg-emerald-50/50 p-4 text-center transition hover:bg-emerald-50"
                               :class="status === 'rejected' ? 'border-rose-300 !bg-rose-50/50 hover:!bg-rose-50' : 'border-emerald-300'">
                            <template x-if="preview"><img :src="preview" alt="" class="absolute inset-0 h-full w-full object-cover"></template>
                            <div x-show="!preview">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm" :class="status === 'rejected' && '!text-rose-600'"><i data-lucide="camera" class="h-6 w-6"></i></span>
                                <p class="mt-3 text-sm font-bold text-ink" x-text="status === 'rejected' ? 'Ambil foto kondisi asli di lokasi' : 'Ambil foto hasil perbaikan'">Ambil foto hasil perbaikan</p>
                                <p class="mt-0.5 text-xs text-slate-500">Otomatis dikompres · maks. 10 MB</p>
                            </div>
                            <div x-show="preview" x-cloak class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-3 text-left text-xs font-bold text-white"><span x-text="name"></span> · ketuk untuk mengganti</div>
                            <input id="photo" name="photo" type="file" accept="image/*" capture="environment" class="sr-only"
                                   @change="const f = $event.target.files[0]; if (f) { name = f.name; const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
                        </label>
                        @error('photo') <p class="mt-1.5 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" :disabled="sending" :class="status === 'rejected' && '!bg-rose-600 hover:!bg-rose-700'" class="btn-primary w-full py-3 text-base">
                        <i data-lucide="save" class="h-5 w-5"></i> <span x-text="sending ? 'Menyimpan…' : (status === 'done' ? 'Tandai selesai' : (status === 'verified' ? 'Verifikasi laporan' : (status === 'rejected' ? 'Tolak laporan' : 'Simpan progres')))"></span>
                    </button>
                </div>
            </form>
        @endif

        <div class="card mt-6 p-6">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">Progres</h3>
            <div class="mt-5"><x-stepper :status="$report->status" vertical /></div>
        </div>
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
