<!DOCTYPE html>
<html lang="id">
@section('title', 'Daftar — SIGAP')
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white font-sans text-slate-700 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    <aside class="relative hidden overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-ink p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute inset-0 opacity-[.08]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 26px 26px"></div>
        <div class="absolute -bottom-24 -right-16 h-96 w-96 rounded-full bg-orange-400/20 blur-3xl"></div>

        <a href="{{ route('home') }}" class="relative">@include('partials.logo', ['dark' => true])</a>

        <div class="relative">
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight">Jadi mata dan telinga<br><span class="text-orange-300">lingkunganmu.</span></h2>
            <p class="mt-4 max-w-md text-brand-100">Gratis dan hanya butuh satu menit. Setelah mendaftar kamu bisa langsung melapor dan mendukung laporan warga lain.</p>

            <ul class="mt-10 space-y-4 text-sm font-semibold">
                @foreach (['Laporkan dengan foto dan titik lokasi di peta', 'Pantau status: Diajukan → Diverifikasi → Dikerjakan → Selesai', 'Beri dukungan pada laporan yang mendesak'] as $t)
                    <li class="flex items-start gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange-400 text-ink"><i data-lucide="check" class="h-3.5 w-3.5"></i></span>{{ $t }}</li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-brand-200/70">&copy; {{ date('Y') }} SIGAP — Sistem Gadget Aduan Publik</p>
    </aside>

    <main class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="lg:hidden">@include('partials.logo')</a>

            <h1 class="mt-8 text-3xl font-extrabold tracking-tight text-ink lg:mt-0">Buat akun warga</h1>
            <p class="mt-2 text-slate-500">Daftar untuk mulai melapor.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
                @csrf
                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Budi Santoso" class="input @error('name') !border-rose-400 @enderror">
                    @error('name') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com" class="input @error('email') !border-rose-400 @enderror">
                    @error('email') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="label">No. HP <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" inputmode="tel" placeholder="08xxxxxxxxxx" class="input @error('phone') !border-rose-400 @enderror">
                        @error('phone') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="region_id" class="label">Wilayah</label>
                        <select id="region_id" name="region_id" required class="input @error('region_id') !border-rose-400 @enderror">
                            <option value="">Pilih wilayah…</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>{{ $region->name }}</option>
                            @endforeach
                        </select>
                        @error('region_id') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="label">Kata sandi</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Min. 8 karakter" class="input @error('password') !border-rose-400 @enderror">
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Ulangi" class="input">
                    </div>
                </div>
                @error('password') <p class="-mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary mt-2 w-full py-3 text-base">Daftar &amp; Mulai Melapor</button>
            </form>

            <p class="mt-8 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-brand-700 hover:underline">Masuk</a></p>
        </div>
    </main>
</div>
@include('partials.scripts')
</body>
</html>
