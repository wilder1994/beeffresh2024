@props([
    'status' => 'ok',
    'value',
])

@php
    $hints = [
        'low' => 'Bajo',
        'out' => 'Agotado',
    ];
@endphp

<span @class([
    'bf-catalog-stock-value tabular-nums',
    'bf-catalog-stock-value--low' => $status === 'low',
    'bf-catalog-stock-value--out' => $status === 'out',
])>
    {{ $value }}
    @if(isset($hints[$status]))
        <span class="bf-catalog-stock-value__hint">{{ $hints[$status] }}</span>
    @endif
</span>
