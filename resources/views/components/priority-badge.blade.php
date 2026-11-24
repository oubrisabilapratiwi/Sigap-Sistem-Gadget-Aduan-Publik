@props(['report'])
{{-- Skala prioritas otomatis: dihitung dari jumlah dukungan (lihat Report::getIsPriorityAttribute) --}}
@if ($report->is_priority)
    <span {{ $attributes->merge(['class' => 'chip ring-1 ring-inset bg-orange-500 text-white ring-orange-600/40']) }} title="Dukungan {{ \App\Models\Report::PRIORITY_THRESHOLD }} atau lebih">
        <i data-lucide="flame" class="h-3.5 w-3.5"></i>{{ $report->priority_label }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'chip ring-1 ring-inset bg-slate-100 text-slate-500 ring-slate-300/70']) }} title="Dukungan kurang dari {{ \App\Models\Report::PRIORITY_THRESHOLD }}">
        <i data-lucide="minus" class="h-3.5 w-3.5"></i>{{ $report->priority_label }}
    </span>
@endif