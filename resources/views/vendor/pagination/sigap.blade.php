@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <p class="text-sm text-slate-500">
            Menampilkan <b class="text-ink">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</b>
            dari <b class="text-ink">{{ $paginator->total() }}</b> laporan
        </p>

        <ul class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <li class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-300"><i data-lucide="chevron-left" class="h-4 w-4"></i></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-600 ring-1 ring-slate-200 transition hover:bg-brand-50 hover:text-brand-700"><i data-lucide="chevron-left" class="h-4 w-4"></i></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-1 text-slate-400">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page" class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-brand-600 px-3 text-sm font-bold text-white shadow-sm">{{ $page }}</li>
                        @else
                            <li><a href="{{ $url }}" class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-white px-3 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 transition hover:bg-brand-50 hover:text-brand-700">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-600 ring-1 ring-slate-200 transition hover:bg-brand-50 hover:text-brand-700"><i data-lucide="chevron-right" class="h-4 w-4"></i></a></li>
            @else
                <li class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-300"><i data-lucide="chevron-right" class="h-4 w-4"></i></li>
            @endif
        </ul>
    </nav>
@endif
