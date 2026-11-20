@props(['status'])
<span {{ $attributes->merge(['class' => 'chip ring-1 ring-inset ' . $status->badge()]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $status->dot() }}"></span>{{ $status->label() }}
</span>
