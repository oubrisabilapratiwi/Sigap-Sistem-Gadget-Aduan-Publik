@if (session('success') || session('error'))
    @php $ok = (bool) session('success'); @endphp
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 7000)" x-show="show" x-transition.opacity
         class="container-x pt-5">
        <div class="flex items-start gap-3 rounded-2xl border px-4 py-3.5 text-sm font-medium shadow-soft
                    {{ $ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
            <i data-lucide="{{ $ok ? 'circle-check' : 'circle-alert' }}" class="mt-0.5 h-5 w-5 shrink-0"></i>
            <p class="flex-1">{{ session('success') ?? session('error') }}</p>
            <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Tutup"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
    </div>
@endif
