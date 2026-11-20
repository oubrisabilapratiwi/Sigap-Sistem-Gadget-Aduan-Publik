@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();

    $nav = $isAdmin
        ? [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'icon' => 'inbox', 'label' => 'Laporan Masuk', 'badge' => $pendingCount ?? 0],
            ['route' => 'admin.officers.index', 'match' => 'admin.officers.*', 'icon' => 'hard-hat', 'label' => 'Petugas Lapangan'],
        ]
        : [
            ['route' => 'officer.tasks.index', 'match' => 'officer.tasks.*', 'icon' => 'clipboard-list', 'label' => 'Tugas Saya'],
        ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100/70 font-sans text-slate-700 antialiased" x-data="{ side: false }">

    {{-- Sidebar --}}
    <aside :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-ink text-slate-300 transition-transform duration-200">
        <div class="flex h-[72px] items-center px-6">
            <a href="{{ route('home') }}">@include('partials.logo', ['dark' => true])</a>
        </div>

        <div class="px-4 pb-2 pt-4 text-[11px] font-bold uppercase tracking-[.16em] text-slate-500">
            {{ $isAdmin ? 'Panel Admin' : 'Panel Petugas' }}
        </div>

        <nav class="flex-1 space-y-1 px-3">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition
                          {{ $active ? 'bg-brand-600 text-white shadow-lg shadow-brand-900/40' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></i>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if (!empty($item['badge']))
                        <span class="rounded-full bg-orange-400 px-2 py-0.5 text-[11px] font-extrabold text-ink">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach

            <div class="my-4 border-t border-white/10"></div>

            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-white/5 hover:text-white">
                <i data-lucide="globe" class="h-[18px] w-[18px]"></i> Lihat Situs Publik
            </a>
            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-white/5 hover:text-white">
                <i data-lucide="map" class="h-[18px] w-[18px]"></i> Peta &amp; Laporan Warga
            </a>
        </nav>

        <div class="m-3 rounded-2xl bg-white/5 p-3">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">{{ $user->initials }}</span>
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-sm font-bold text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ $user->roleLabel() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="rounded-lg p-2 text-slate-400 transition hover:bg-white/10 hover:text-rose-300" title="Keluar" aria-label="Keluar">
                        <i data-lucide="log-out" class="h-[18px] w-[18px]"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div x-show="side" x-cloak @click="side = false" class="fixed inset-0 z-30 bg-ink/60 backdrop-blur-sm lg:hidden"></div>

    {{-- Konten --}}
    <div class="lg:pl-72">
        <header class="sticky top-0 z-20 flex h-[72px] items-center gap-4 border-b border-slate-200/80 bg-white/80 px-4 backdrop-blur-xl sm:px-8">
            <button @click="side = true" class="rounded-xl border border-slate-200 p-2.5 lg:hidden" aria-label="Menu"><i data-lucide="menu" class="h-5 w-5"></i></button>
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-lg font-extrabold tracking-tight text-ink sm:text-xl">@yield('heading')</h1>
                @hasSection('subheading')
                    <p class="hidden truncate text-sm text-slate-500 sm:block">@yield('subheading')</p>
                @endif
            </div>
            @yield('actions')
        </header>

        @include('partials.flash')

        <main class="px-4 py-8 sm:px-8">@yield('content')</main>
    </div>

    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
