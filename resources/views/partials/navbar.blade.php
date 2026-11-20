@php
    $link = fn ($active) => $active
        ? 'text-brand-700 bg-brand-50'
        : 'text-slate-600 hover:text-ink hover:bg-slate-100';
@endphp

<header x-data="{ menu: false, user: false }" class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl">
    <div class="container-x flex h-[72px] items-center justify-between gap-6">
        <a href="{{ route('home') }}" aria-label="SIGAP — beranda">@include('partials.logo')</a>

        <nav class="hidden items-center gap-1 md:flex">
            <a href="{{ route('home') }}" class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $link(request()->routeIs('home')) }}">Beranda</a>
            <a href="{{ route('reports.index') }}" class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $link(request()->routeIs('reports.index', 'reports.show')) }}">Laporan Warga</a>
            <a href="{{ route('home') }}#cara-kerja" class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $link(false) }}">Cara Kerja</a>
            @auth
                @can('report.create')
                    <a href="{{ route('reports.mine') }}" class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $link(request()->routeIs('reports.mine')) }}">Laporan Saya</a>
                @endcan
            @endauth
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            @guest
                <a href="{{ route('login') }}" class="btn-outline">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
            @endguest

            @auth
                @can('report.create')
                    <a href="{{ route('reports.create') }}" class="btn-primary">
                        <i data-lucide="plus" class="h-4 w-4"></i> Buat Laporan
                    </a>
                @endcan

                <div class="relative">
                    <button @click="user = !user" @click.outside="user = false" class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 transition hover:bg-slate-50">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-xs font-bold text-white">{{ auth()->user()->initials }}</span>
                        <span class="text-left leading-tight">
                            <span class="block max-w-[120px] truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</span>
                            <span class="block text-[11px] text-slate-500">{{ auth()->user()->roleLabel() }}</span>
                        </span>
                        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
                    </button>

                    <div x-show="user" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-slate-200">
                        @can('admin.access')
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i data-lucide="layout-dashboard" class="h-4 w-4 text-brand-600"></i> Dashboard Admin</a>
                        @endcan
                        @can('officer.access')
                            <a href="{{ route('officer.tasks.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i data-lucide="clipboard-list" class="h-4 w-4 text-brand-600"></i> Tugas Saya</a>
                        @endcan
                        @can('report.create')
                            <a href="{{ route('reports.mine') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i data-lucide="folder-open" class="h-4 w-4 text-brand-600"></i> Laporan Saya</a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50"><i data-lucide="log-out" class="h-4 w-4"></i> Keluar</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>

        <button @click="menu = !menu" class="rounded-xl border border-slate-200 p-2.5 md:hidden" aria-label="Menu">
            <i x-show="!menu" data-lucide="menu" class="h-5 w-5"></i>
            <i x-show="menu" x-cloak data-lucide="x" class="h-5 w-5"></i>
        </button>
    </div>

    {{-- Menu mobile --}}
    <div x-show="menu" x-cloak x-transition class="border-t border-slate-200 bg-white md:hidden">
        <div class="container-x flex flex-col gap-1 py-4">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Beranda</a>
            <a href="{{ route('reports.index') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Laporan Warga</a>
            <a href="{{ route('home') }}#cara-kerja" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cara Kerja</a>
            @guest
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="btn-outline">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
                </div>
            @endguest
            @auth
                @can('report.create')
                    <a href="{{ route('reports.mine') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Laporan Saya</a>
                    <a href="{{ route('reports.create') }}" class="btn-primary mt-2"><i data-lucide="plus" class="h-4 w-4"></i> Buat Laporan</a>
                @endcan
                @can('admin.access')
                    <a href="{{ route('admin.dashboard') }}" class="btn-outline mt-2">Dashboard Admin</a>
                @endcan
                @can('officer.access')
                    <a href="{{ route('officer.tasks.index') }}" class="btn-outline mt-2">Tugas Saya</a>
                @endcan
                <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf
                    <button class="btn-outline w-full text-rose-600">Keluar ({{ auth()->user()->name }})</button>
                </form>
            @endauth
        </div>
    </div>
</header>
