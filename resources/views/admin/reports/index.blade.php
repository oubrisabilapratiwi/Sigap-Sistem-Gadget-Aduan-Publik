@extends('layouts.panel')

@section('title', 'Laporan Masuk — SIGAP')
@section('heading', 'Laporan Masuk')
@section('subheading', 'Disposisikan laporan ke petugas lapangan untuk dicek di lokasi')

@section('content')
@php
    $current = $filters['status'] ?? '';
    $sort = $filters['sort'] ?? 'populer';
    $allCount = $tabCounts->sum();
    $tab = fn ($s) => route('admin.reports.index', array_filter(array_merge($filters, ['status' => $s, 'page' => null])));
@endphp

{{-- Tab status --}}
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ $tab(null) }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition {{ $current === '' ? 'bg-ink text-white shadow-lg' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
        Semua <span class="rounded-full {{ $current === '' ? 'bg-white/20' : 'bg-slate-100' }} px-2 py-0.5 text-xs">{{ $allCount }}</span>
    </a>
    @foreach (\App\Enums\ReportStatus::cases() as $s)
        <a href="{{ $tab($s->value) }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition {{ $current === $s->value ? 'bg-ink text-white shadow-lg' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
            <span class="h-2 w-2 rounded-full {{ $s->dot() }}"></span>{{ $s->label() }}
            <span class="rounded-full {{ $current === $s->value ? 'bg-white/20' : 'bg-slate-100' }} px-2 py-0.5 text-xs">{{ $tabCounts[$s->value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('admin.reports.index') }}" class="card mt-5 grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
    <input type="hidden" name="status" value="{{ $current }}">
    <div class="relative">
        <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-400"></i>
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul, alamat, kode…" class="input pl-11">
    </div>
    <select name="category" class="input" onchange="this.form.submit()">
        <option value="">Semua kategori</option>
        @foreach ($categories as $c)<option value="{{ $c->slug }}" @selected(($filters['category'] ?? '') === $c->slug)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="region" class="input" onchange="this.form.submit()">
        <option value="">Semua wilayah</option>
        @foreach ($regions as $r)<option value="{{ $r->id }}" @selected(($filters['region'] ?? '') == $r->id)>{{ $r->name }}</option>@endforeach
    </select>
    <select name="sort" class="input" onchange="this.form.submit()">
        <option value="populer" @selected($sort === 'populer')>Dukungan terbanyak</option>
        <option value="terbaru" @selected($sort === 'terbaru')>Terbaru</option>
        <option value="terlama" @selected($sort === 'terlama')>Terlama</option>
    </select>
    <button class="btn-primary" type="submit">Terapkan</button>
</form>

{{-- Tabel --}}
<div class="card mt-5 overflow-hidden">
    @if ($reports->isEmpty())
        <div class="flex flex-col items-center px-6 py-20 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i data-lucide="inbox" class="h-7 w-7"></i></span>
            <p class="mt-4 font-extrabold text-ink">Tidak ada laporan</p>
            <p class="mt-1 text-sm text-slate-500">Coba ubah filter atau status.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3.5">Laporan</th>
                        <th class="px-4 py-3.5">Wilayah</th>
                        <th class="px-4 py-3.5 text-center">Dukungan</th>
                        <th class="whitespace-nowrap px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Petugas</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($reports as $report)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.reports.show', $report) }}" class="group flex items-center gap-4">
                                    <span class="relative h-14 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                        @if ($report->photo)
                                            <img src="{{ $report->thumb_url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                        @else
                                            <span class="flex h-full items-center justify-center" style="color: {{ $report->category->color }}"><i data-lucide="{{ $report->category->icon }}" class="h-6 w-6"></i></span>
                                        @endif
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block max-w-xs truncate font-bold text-ink group-hover:text-brand-700">{{ $report->title }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ $report->tracking_code }} · {{ $report->user->name }} · {{ $report->created_at->diffForHumans() }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $report->region->name }}</td>
                            <td class="px-4 py-4 text-center"><span class="inline-flex items-center gap-1 font-extrabold text-ink"><i data-lucide="arrow-big-up" class="h-4 w-4 text-brand-600"></i>{{ $report->upvotes_count }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="flex flex-col items-start gap-1.5">
                                    <x-status-badge :status="$report->status" />
                                    <x-priority-badge :report="$report" />
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600">
                                {{ $report->assignee?->name ?? '—' }}
                                @if ($report->assignee?->agency_label)<span class="block text-xs text-slate-400">{{ $report->assignee->agency_label }}</span>@endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.reports.show', $report) }}" class="{{ $report->status->value === 'submitted' && ! $report->assigned_to ? 'btn-primary' : 'btn-outline' }} !px-3.5 !py-2">
                                    {{ $report->status->value === 'submitted' && ! $report->assigned_to ? 'Disposisi' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $reports->links() }}</div>
    @endif
</div>
@endsection