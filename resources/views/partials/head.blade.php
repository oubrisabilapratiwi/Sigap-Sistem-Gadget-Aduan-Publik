<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'SIGAP — Sistem Gadget Aduan Publik')</title>
<meta name="description" content="SIGAP (Sistem Gadget Aduan Publik): laporkan kerusakan fasilitas umum di lingkunganmu dan pantau penanganannya secara transparan.">
<link rel="icon" type="image/png" href="{{ asset('images/sigap-favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/sigap-icon-512.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

{{-- Tailwind via CDN agar proyek langsung jalan tanpa npm. Untuk produksi, build dengan Vite (lihat README). --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    brand: {
                        50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa',
                        500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a', 950: '#172554',
                    },
                    ink: { DEFAULT: '#0b1437', 800: '#111d4d', 700: '#1b2b6b' },
                },
                boxShadow: {
                    soft: '0 1px 2px rgba(15,23,42,.04), 0 8px 24px -10px rgba(15,23,42,.12)',
                    lift: '0 2px 4px rgba(15,23,42,.05), 0 22px 44px -14px rgba(37,99,235,.30)',
                },
                keyframes: {
                    float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } },
                    rise: { from: { opacity: 0, transform: 'translateY(14px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
                },
                animation: { float: 'float 6s ease-in-out infinite', rise: 'rise .6s ease-out both' },
            },
        },
    };
</script>

<style type="text/tailwindcss">
    @layer components {
        .container-x { @apply mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8; }
        .label { @apply mb-1.5 block text-sm font-semibold text-slate-700; }
        .input {
            @apply block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800
                   shadow-sm placeholder:text-slate-400 transition
                   focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15;
        }
        .btn { @apply inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60; }
        .btn-primary { @apply btn bg-brand-600 text-white shadow-sm hover:bg-brand-700 focus:ring-brand-500/25; }
        .btn-accent { @apply btn bg-orange-400 text-ink shadow-sm hover:bg-orange-300 focus:ring-orange-400/30; }
        .btn-outline { @apply btn border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-300/40; }
        .btn-danger { @apply btn bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500/25; }
        .card { @apply rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/70; }
        .chip { @apply inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold; }
    }
</style>
<style>
    [x-cloak] { display: none !important; }
    html { -webkit-font-smoothing: antialiased; }
    /* Kurung semua lapisan peta (z-index 400-1000) di dalam satu stacking context,
       supaya tidak menimpa header/navbar yang sticky saat halaman di-scroll. */
    .leaflet-container { font-family: inherit; isolation: isolate; z-index: 0; }
    .leaflet-popup-content-wrapper { border-radius: 14px; }
</style>
