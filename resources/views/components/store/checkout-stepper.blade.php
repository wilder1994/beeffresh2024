@props([
    'current' => 2,
])

@php
    $steps = [
        1 => ['label' => 'Carrito', 'done' => $current > 1],
        2 => ['label' => 'Confirmar', 'done' => $current > 2, 'active' => $current === 2],
        3 => ['label' => 'Listo', 'done' => false, 'active' => $current === 3],
    ];
@endphp

<ol class="bf-checkout-stepper mb-6" aria-label="Pasos del pedido">
    @foreach($steps as $number => $step)
        <li @class([
            'bf-checkout-stepper__item',
            'bf-checkout-stepper__item--done' => $step['done'] ?? false,
            'bf-checkout-stepper__item--active' => $step['active'] ?? false,
        ])>
            <span class="bf-checkout-stepper__marker" aria-hidden="true">
                @if($step['done'] ?? false)
                    ✓
                @else
                    {{ $number }}
                @endif
            </span>
            <span class="bf-checkout-stepper__label">{{ $step['label'] }}</span>
        </li>
        @if(!$loop->last)
            <li class="bf-checkout-stepper__line" aria-hidden="true"></li>
        @endif
    @endforeach
</ol>
