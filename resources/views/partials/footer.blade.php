<footer class="mt-24 bg-ink text-slate-300">
    <div class="container-x grid gap-10 py-14 md:grid-cols-4">
        <div class="md:col-span-2">
            @include('partials.logo', ['dark' => true])
            <p class="mt-5 max-w-md text-sm leading-relaxed text-slate-400">
                SIGAP menghubungkan warga, admin, dan petugas lapangan dalam satu alur yang transparan —
                dari laporan masuk sampai perbaikan selesai dengan foto bukti.
            </p>
        </div>
        <div>
            <h4 class="text-sm font-bold uppercase tracking-wider text-white">Jelajahi</h4>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a class="hover:text-white" href="{{ route('reports.index') }}">Laporan warga</a></li>
                <li><a class="hover:text-white" href="{{ route('home') }}#cara-kerja">Cara kerja</a></li>
                <li><a class="hover:text-white" href="{{ route('reports.index', ['sort' => 'populer']) }}">Paling mendesak</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-sm font-bold uppercase tracking-wider text-white">Mulai</h4>
            <ul class="mt-4 space-y-2.5 text-sm">
                @guest
                    <li><a class="hover:text-white" href="{{ route('register') }}">Daftar sebagai warga</a></li>
                    <li><a class="hover:text-white" href="{{ route('login') }}">Masuk</a></li>
                @else
                    <li><a class="hover:text-white" href="{{ route('reports.create') }}">Buat laporan</a></li>
                @endguest
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="container-x flex flex-col items-center justify-between gap-2 py-6 text-xs text-slate-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} SIGAP — Sistem Gadget Aduan Publik. Dibuat untuk lingkungan yang lebih baik.</p>
            <p>Peta oleh <a class="underline hover:text-slate-300" href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a></p>
        </div>
    </div>
</footer>
