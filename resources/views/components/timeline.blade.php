@props(['updates'])
<ol class="relative space-y-6">
    @foreach ($updates as $update)
        @php $s = $update->status; @endphp
        <li class="relative flex gap-4">
            @if (! $loop->last)
                <span class="absolute left-[17px] top-10 h-[calc(100%-1rem)] w-0.5 bg-slate-200"></span>
            @endif
            <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white ring-4 ring-white {{ $s->dot() }}">
                <i data-lucide="{{ $s->icon() }}" class="h-4 w-4"></i>
            </span>
            <div class="min-w-0 flex-1 pb-1">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <x-status-badge :status="$s" />
                    <span class="text-xs text-slate-400">{{ $update->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>
                @if ($update->note)
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $update->note }}</p>
                @endif
                @if ($update->user)
                    <p class="mt-1 text-xs text-slate-400">oleh {{ $update->user->isCitizen() ? $update->user->public_name : $update->user->name }}</p>
                @endif
                @if ($update->photo)
                    <button type="button" onclick="openLightbox('{{ $update->photo_url }}')"
                            class="mt-3 block overflow-hidden rounded-xl ring-1 ring-slate-200">
                        <img src="{{ $update->thumb_url }}" alt="Foto bukti" loading="lazy" class="h-28 w-44 cursor-zoom-in object-cover transition hover:scale-105">
                    </button>
                @endif
            </div>
        </li>
    @endforeach
</ol>
