@extends('layouts.panel')

@section('title', 'Petugas Lapangan — SIGAP')
@section('heading', 'Petugas Lapangan')
@section('subheading', 'Kelola akun petugas dinas terkait')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1fr_380px]">

    <section class="card overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="font-extrabold text-ink">Daftar petugas <span class="ml-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">{{ $officers->count() }}</span></h2>
        </div>

        @forelse ($officers as $o)
            <div class="border-b border-slate-100 px-6 py-5 last:border-0 {{ $o->is_active ? '' : 'bg-slate-50/70' }}">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white {{ $o->is_active ? 'bg-ink' : 'bg-slate-400' }}">{{ $o->initials }}</span>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="flex flex-wrap items-center gap-x-2 gap-y-1 font-extrabold text-ink">
                            <span class="break-words">{{ $o->name }}</span>
                            @unless ($o->is_active)<span class="chip bg-slate-200 text-slate-600">Nonaktif</span>@endunless
                        </p>
                        <p class="mt-1 break-all text-sm text-slate-500">{{ $o->email }}</p>
                        @if ($o->phone)<p class="mt-0.5 text-sm text-slate-400">{{ $o->phone }}</p>@endif
                    </div>
                    <form method="POST" action="{{ route('admin.officers.toggle', $o) }}" class="shrink-0"
                          data-confirm="{{ $o->is_active ? $o->name . ' tidak akan bisa masuk dan tidak muncul lagi dalam pilihan disposisi. Tugas yang sedang berjalan tidak berubah.' : $o->name . ' akan bisa masuk kembali dan menerima tugas baru.' }}"
                          data-confirm-title="{{ $o->is_active ? 'Nonaktifkan akun petugas?' : 'Aktifkan kembali akun petugas?' }}"
                          data-confirm-ok="{{ $o->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan' }}"
                          data-confirm-tone="{{ $o->is_active ? 'danger' : 'primary' }}"
                          data-confirm-icon="{{ $o->is_active ? 'disable' : 'enable' }}">
                        @csrf @method('PATCH')
                        <button class="btn-outline !px-3 !py-2 {{ $o->is_active ? 'text-rose-600' : 'text-emerald-700' }}" title="{{ $o->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                            <i data-lucide="{{ $o->is_active ? 'user-x' : 'user-check' }}" class="h-4 w-4"></i> <span class="hidden sm:inline">{{ $o->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                        </button>
                    </form>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3 sm:pl-16">
                    <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                        @if ($o->agency_label)
                            <span class="chip min-w-0 max-w-full !items-start !rounded-xl text-left leading-tight bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-200"><i data-lucide="building-2" class="mt-px h-3.5 w-3.5 shrink-0"></i><span class="min-w-0">{{ $o->agency_label }}</span></span>
                        @else
                            <span class="chip whitespace-nowrap bg-slate-100 text-slate-400"><i data-lucide="building-2" class="h-3.5 w-3.5"></i>Instansi belum diisi</span>
                        @endif
                        <span class="chip whitespace-nowrap bg-slate-100 text-slate-600"><i data-lucide="map-pin" class="h-3.5 w-3.5"></i>{{ $o->region?->name ?? '—' }}</span>
                    </div>
                    <div class="flex shrink-0 items-center gap-2 text-center">
                        <div class="rounded-xl bg-amber-50 px-3 py-1.5"><p class="text-lg font-extrabold leading-none text-amber-700">{{ $o->active_count }}</p><p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-amber-600">Aktif</p></div>
                        <div class="rounded-xl bg-emerald-50 px-3 py-1.5"><p class="text-lg font-extrabold leading-none text-emerald-700">{{ $o->done_count }}</p><p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-emerald-600">Selesai</p></div>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center text-slate-500">Belum ada petugas. Tambahkan lewat formulir di samping.</div>
        @endforelse
    </section>

    <form method="POST" action="{{ route('admin.officers.store') }}" class="card h-fit space-y-4 p-6 xl:sticky xl:top-24">
        @csrf
        <div>
            <h2 class="flex items-center gap-2 font-extrabold text-ink"><i data-lucide="user-plus" class="h-5 w-5 text-brand-600"></i> Tambah petugas</h2>
            <p class="mt-1 text-sm text-slate-500">Akun langsung aktif dengan peran Petugas Lapangan.</p>
        </div>

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="input @error('name') !border-rose-400 @enderror">
            @error('name') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input @error('email') !border-rose-400 @enderror">
            @error('email') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div>
                <label for="phone" class="label">No. HP</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" class="input">
            </div>
            <div>
                <label for="region_id" class="label">Wilayah tugas</label>
                <select id="region_id" name="region_id" required class="input @error('region_id') !border-rose-400 @enderror">
                    <option value="">Pilih…</option>
                    @foreach ($regions as $r)<option value="{{ $r->id }}" @selected(old('region_id') == $r->id)>{{ $r->name }}</option>@endforeach
                </select>
                @error('region_id') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label for="agency" class="label">Instansi / asal dinas</label>
            <select id="agency" name="agency" required class="input @error('agency') !border-rose-400 @enderror">
                <option value="">Pilih…</option>
                @foreach ($agencies as $a)<option value="{{ $a->value }}" @selected(old('agency') === $a->value)>{{ $a->label() }}</option>@endforeach
            </select>
            @error('agency') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">Kata sandi awal</label>
            <input id="password" name="password" type="text" minlength="8" required placeholder="Min. 8 karakter" class="input @error('password') !border-rose-400 @enderror">
            @error('password') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <button class="btn-primary w-full"><i data-lucide="plus" class="h-4 w-4"></i> Buat akun petugas</button>
    </form>
</div>
@endsection