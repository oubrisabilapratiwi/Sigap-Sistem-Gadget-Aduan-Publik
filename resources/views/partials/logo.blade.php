@php $dark = $dark ?? false; @endphp
<span class="inline-flex items-center gap-2.5">
    <img src="{{ asset('images/sigap-logo.png') }}" alt="Logo SIGAP" width="244" height="320"
         class="h-11 w-auto shrink-0 drop-shadow-sm">
    <span class="leading-none">
        <span class="block text-xl font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-ink' }}">SIGAP</span>
        <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-[.18em] {{ $dark ? 'text-brand-300' : 'text-brand-700' }}">Aduan Publik</span>
    </span>
</span>
