@extends('layouts.app')

@section('title', 'Buat Laporan — SIGAP')

@push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush

@section('content')
@php
    $center = config('sigap.map_center');
    $lat = old('latitude', $center[0]);
    $lng = old('longitude', $center[1]);
@endphp

<div class="container-x py-10 sm:py-12">
    <div class="mx-auto max-w-4xl">
        <span class="text-sm font-extrabold uppercase tracking-widest text-brand-600">Laporan baru</span>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Apa yang rusak di lingkunganmu?</h1>
        <p class="mt-2 text-slate-500">Lengkapi foto, kategori, dan titik lokasi. Foto otomatis dikompres agar ringan.</p>

        <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6"
              x-data="reportForm()" @submit="sending = true">
            @csrf

            {{-- 1. Foto --}}
            <section class="card p-6 sm:p-8">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-sm font-extrabold text-white">1</span>
                    <h2 class="text-lg font-extrabold text-ink">Foto bukti</h2>
                </div>

                <label for="photo" @dragover.prevent="drag = true" @dragleave.prevent="drag = false" @drop.prevent="drag = false; drop($event)"
                       :class="drag ? 'border-brand-500 bg-brand-50' : 'border-slate-300 bg-slate-50 hover:border-brand-400 hover:bg-brand-50/50'"
                       class="relative flex min-h-[220px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed p-6 text-center transition">
                    <template x-if="preview">
                        <img :src="preview" alt="Pratinjau foto" class="absolute inset-0 h-full w-full object-cover">
                    </template>
                    <div x-show="!preview" class="relative">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-sm"><i data-lucide="camera" class="h-7 w-7"></i></span>
                        <p class="mt-4 font-extrabold text-ink">Ambil foto atau pilih dari galeri</p>
                        <p class="mt-1 text-sm text-slate-500">JPG, PNG, atau WEBP · maks. 10 MB · diseret ke sini juga bisa</p>
                    </div>
                    <div x-show="preview" x-cloak class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4 text-left text-sm font-bold text-white">
                        <span x-text="fileName"></span> · klik untuk mengganti
                    </div>
                    <input id="photo" name="photo" type="file" accept="image/*" class="sr-only" x-ref="file" @change="pick($event)">
                </label>
                @error('photo') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            </section>

            {{-- 2. Detail --}}
            <section class="card p-6 sm:p-8">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-sm font-extrabold text-white">2</span>
                    <h2 class="text-lg font-extrabold text-ink">Jenis kerusakan</h2>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($categories as $c)
                        <label class="cursor-pointer">
                            <input type="radio" name="category_id" value="{{ $c->id }}" class="peer sr-only" @checked(old('category_id') == $c->id) required>
                            <span class="flex h-full flex-col items-center gap-2 rounded-2xl border-2 border-slate-200 bg-white p-4 text-center transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:shadow-lift hover:border-slate-300 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-500/20">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl" style="background: {{ $c->color }}1f; color: {{ $c->color }}"><i data-lucide="{{ $c->icon }}" class="h-5 w-5"></i></span>
                                <span class="text-xs font-bold leading-tight text-ink">{{ $c->name }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('category_id') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="title" class="label">Judul laporan</label>
                        <input id="title" name="title" value="{{ old('title') }}" maxlength="120" required placeholder="Contoh: Lubang besar di depan SDN 03 membahayakan pengendara" class="input @error('title') !border-rose-400 @enderror">
                        @error('title') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="label">Deskripsi</label>
                        <textarea id="description" name="description" rows="5" maxlength="2000" required placeholder="Ceritakan kondisinya: sudah berapa lama, seberapa parah, dan apa dampaknya bagi warga." class="input @error('description') !border-rose-400 @enderror">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- 3. Lokasi --}}
            <section class="card p-6 sm:p-8">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-sm font-extrabold text-white">3</span>
                    <h2 class="text-lg font-extrabold text-ink">Titik lokasi</h2>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <button type="button" @click="locate()" class="btn-outline" :disabled="locating">
                        <i data-lucide="locate-fixed" class="h-4 w-4"></i> <span x-text="locating ? 'Mencari lokasi…' : 'Gunakan lokasi saya'"></span>
                    </button>
                    <p class="text-sm text-slate-500">atau klik / geser pin pada peta ke titik kerusakan.</p>
                </div>

                <div class="relative">
                    <div id="pick-map" class="h-80 w-full rounded-2xl ring-1 ring-slate-200"></div>
                </div>
                <input type="hidden" name="latitude" x-model="lat">
                <input type="hidden" name="longitude" x-model="lng">
                @error('latitude') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-slate-400">Koordinat: <span x-text="Number(lat).toFixed(6)"></span>, <span x-text="Number(lng).toFixed(6)"></span></p>

                <div class="mt-6 grid gap-5 sm:grid-cols-[1fr_220px]">
                    <div>
                        <label for="address" class="label">Alamat / patokan</label>
                        <input id="address" name="address" x-model="address" @input="addressEdited = true" maxlength="255" required placeholder="Jl. Melati Raya No. 12, depan SDN 03" class="input @error('address') !border-rose-400 @enderror">
                        <p x-show="geocoding" x-cloak class="mt-1.5 text-xs text-slate-400">Mencari alamat dari peta…</p>
                        @error('address') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="region_id" class="label">Wilayah</label>
                        <select id="region_id" name="region_id" required class="input @error('region_id') !border-rose-400 @enderror">
                            <option value="">Pilih…</option>
                            @foreach ($regions as $r)
                                <option value="{{ $r->id }}" @selected(old('region_id', auth()->user()->region_id) == $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('region_id') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse items-stretch justify-between gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('reports.index') }}" class="btn-outline">Batal</a>
                <button type="submit" class="btn-primary px-8 py-3.5 text-base" :disabled="sending">
                    <i data-lucide="send" class="h-5 w-5"></i> <span x-text="sending ? 'Mengirim…' : 'Kirim Laporan'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function reportForm() {
        return {
            lat: @json((float) $lat), lng: @json((float) $lng),
            address: @json(old('address', '')),
            addressEdited: {{ old('address') ? 'true' : 'false' }},
            hasPoint: {{ old('latitude') ? 'true' : 'false' }},
            preview: null, fileName: '', drag: false,
            locating: false, geocoding: false, sending: false,
            map: null, marker: null,

            init() {
                this.map = L.map('pick-map').setView([this.lat, this.lng], this.hasPoint ? 17 : 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(this.map);

                this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', () => { const p = this.marker.getLatLng(); this.setPoint(p.lat, p.lng); });
                this.map.on('click', (e) => this.setPoint(e.latlng.lat, e.latlng.lng));
            },

            setPoint(lat, lng, zoom = false) {
                this.lat = lat; this.lng = lng; this.hasPoint = true;
                this.marker.setLatLng([lat, lng]);
                if (zoom) this.map.setView([lat, lng], 17);
                this.reverse(lat, lng);
            },

            locate() {
                if (!navigator.geolocation) return alert('Peramban Anda tidak mendukung geolokasi.');
                this.locating = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => { this.locating = false; this.setPoint(pos.coords.latitude, pos.coords.longitude, true); },
                    () => { this.locating = false; alert('Tidak dapat mengambil lokasi. Izinkan akses lokasi atau pilih manual di peta.'); },
                    { enableHighAccuracy: true, timeout: 12000 }
                );
            },

            // Isi alamat otomatis (OpenStreetMap Nominatim) selama alamat belum diketik manual
            async reverse(lat, lng) {
                if (this.addressEdited) return;
                this.geocoding = true;
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=id&lat=${lat}&lon=${lng}`);
                    const data = await res.json();
                    if (data.display_name && !this.addressEdited) this.address = data.display_name.split(', ').slice(0, 4).join(', ');
                } catch (e) { /* abaikan: warga bisa isi manual */ }
                this.geocoding = false;
            },

            pick(e) { this.show(e.target.files[0]); },
            drop(e) {
                const file = e.dataTransfer.files[0];
                if (!file) return;
                const dt = new DataTransfer(); dt.items.add(file);
                this.$refs.file.files = dt.files;
                this.show(file);
            },
            show(file) {
                if (!file || !file.type.startsWith('image/')) return;
                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (ev) => this.preview = ev.target.result;
                reader.readAsDataURL(file);
            },
        };
    }
</script>
@endpush
