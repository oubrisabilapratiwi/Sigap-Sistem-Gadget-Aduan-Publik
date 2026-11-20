<!DOCTYPE html>
<html lang="id">
@section('title', 'Masuk — SIGAP')
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white font-sans text-slate-700 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Panel kiri --}}
    <aside class="relative hidden overflow-hidden bg-ink p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute inset-0 opacity-[.08]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 26px 26px"></div>
        <div class="absolute -right-20 -top-24 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-16 h-80 w-80 rounded-full bg-orange-400/20 blur-3xl"></div>

        <a href="{{ route('home') }}" class="relative">@include('partials.logo', ['dark' => true])</a>

        <div class="relative">
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight">Suaramu didengar,<br><span class="text-orange-300">progresnya terlihat.</span></h2>
            <p class="mt-4 max-w-md text-slate-300">Masuk untuk membuat laporan, mendukung laporan tetangga, atau mengelola penanganan di lapangan.</p>

            <div class="mt-10 space-y-3">
                @foreach ([['camera', 'Laporan dengan foto & titik lokasi'], ['arrow-big-up', 'Dukungan warga menentukan prioritas'], ['badge-check', 'Selesai hanya bila ada foto bukti']] as [$i, $t])
                    <div class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/20 text-brand-300"><i data-lucide="{{ $i }}" class="h-[18px] w-[18px]"></i></span>
                        <span class="text-sm font-semibold">{{ $t }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="relative text-xs text-slate-500">&copy; {{ date('Y') }} SIGAP — Sistem Gadget Aduan Publik</p>
    </aside>

    {{-- Form --}}
    <main class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="lg:hidden">@include('partials.logo')</a>

            <h1 class="mt-8 text-3xl font-extrabold tracking-tight text-ink lg:mt-0">Selamat datang kembali</h1>
            <p class="mt-2 text-slate-500">Masuk ke akun SIGAP Anda.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@email.com" class="input @error('email') !border-rose-400 @enderror">
                    @error('email') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ show: false }">
                    <label for="password" class="label">Kata sandi</label>
                    <div class="relative">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password" placeholder="••••••••" class="input pr-11">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3.5 text-slate-400 hover:text-slate-600" aria-label="Tampilkan kata sandi">
                            <i x-show="!show" data-lucide="eye" class="h-[18px] w-[18px]"></i>
                            <i x-show="show" x-cloak data-lucide="eye-off" class="h-[18px] w-[18px]"></i>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2.5 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Ingat saya
                </label>

                <button type="submit" class="btn-primary w-full py-3 text-base">Masuk</button>
            </form>

            <p class="mt-8 text-center text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-brand-700 hover:underline">Daftar sebagai warga</a></p>
        </div>
    </main>
</div>
@include('partials.scripts')
</body>
</html>
