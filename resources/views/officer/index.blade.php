@extends('layouts.panel')

@section('title', 'Tugas Saya — SIGAP')
@section('heading', 'Tugas Saya')
@section('subheading', 'Laporan yang didisposisikan kepada Anda')

@section('content')
<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
    @foreach ([
        ['submitted', 'Perlu dicek', $stats['submitted'], 'search-check', 'from-slate-400 to-slate-600'],
        ['verified', 'Siap dikerjakan', $stats['verified'], 'shield-check', 'from-sky-400 to-sky-600'],
        ['in_progress', 'Sedang dikerjakan', $stats['in_progress'], 'hammer', 'from-amber-400 to-orange-500'],
        ['done', 'Selesai', $stats['done'], 'badge-check', 'from-emerald-400 to-emerald-600'],
    ] as [$k, $label, $n, $icon, $grad])
        <div class="card p-4 sm:p-6">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-white shadow-lg sm:h-11 sm:w-11"><i data-lucide="{{ $icon }}" class="h-5 w-5"></i></span>
            <p class="mt-3 text-3xl font-extrabold tracking-tight text-ink sm:mt-4 sm:text-4xl">{{ $n }}</p>
            <p class="mt-0.5 text-xs font-semibold text-slate-500 sm:text-sm">{{ $label }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 inline-flex rounded-xl bg-slate-200/70 p-1">
    <a href="{{ route('officer.tasks.index', ['tab' => 'aktif']) }}" class="rounded-lg px-5 py-2 text-sm font-bold transition {{ $tab !== 'selesai' ? 'bg-white text-ink shadow-sm' : 'text-slate-500' }}">Tugas aktif</a>
    <a href="{{ route('officer.tasks.index', ['tab' => 'selesai']) }}" class="rounded-lg px-5 py-2 text-sm font-bold transition {{ $tab === 'selesai' ? 'bg-white text-ink shadow-sm' : 'text-slate-500' }}">Riwayat selesai</a>
</div>

<div class="mt-6">
    @if ($tasks->isEmpty())
        <div class="card flex flex-col items-center px-6 py-20 text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="{{ $tab === 'selesai' ? 'history' : 'coffee' }}" class="h-8 w-8"></i></span>
            <h3 class="mt-5 text-xl font-extrabold text-ink">{{ $tab === 'selesai' ? 'Belum ada riwayat' : 'Tidak ada tugas aktif' }}</h3>
            <p class="mt-2 max-w-sm text-slate-500">{{ $tab === 'selesai' ? 'Laporan yang Anda selesaikan atau tolak akan muncul di sini.' : 'Tugas baru akan muncul setelah admin mendisposisikan laporan kepada Anda.' }}</p>
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($tasks as $task)
                <a href="{{ route('officer.tasks.show', $task) }}" class="card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lift">
                    <div class="relative aspect-[16/8] bg-slate-100">
                        @if ($task->photo)
                            <img src="{{ $task->thumb_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center" style="background: {{ $task->category->color }}22; color: {{ $task->category->color }}"><i data-lucide="{{ $task->category->icon }}" class="h-10 w-10"></i></div>
                        @endif
                        <x-status-badge :status="$task->status" class="absolute right-3 top-3 bg-white/95 shadow-sm" />
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-ink/80 px-2.5 py-1 text-xs font-bold text-white backdrop-blur"><i data-lucide="arrow-big-up" class="h-3.5 w-3.5"></i>{{ $task->upvotes_count }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-bold" style="color: {{ $task->category->color }}">{{ $task->category->name }}</p>
                        <h3 class="mt-1 line-clamp-2 font-extrabold leading-snug text-ink group-hover:text-brand-700">{{ $task->title }}</h3>
                        <p class="mt-2 flex items-start gap-1.5 text-sm text-slate-500"><i data-lucide="map-pin" class="mt-0.5 h-4 w-4 shrink-0"></i><span class="line-clamp-1">{{ $task->address }}</span></p>
                        <div class="mt-auto flex items-center justify-between pt-4 text-xs text-slate-400">
                            <span>{{ $task->region->name }} · {{ $task->created_at->diffForHumans() }}</span>
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-brand-700">{{ $tab === 'selesai' ? 'Lihat' : ($task->status->value === 'submitted' ? 'Periksa' : 'Kerjakan') }} <i data-lucide="arrow-right" class="h-4 w-4"></i></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $tasks->links() }}</div>
    @endif
</div>
@endsection
