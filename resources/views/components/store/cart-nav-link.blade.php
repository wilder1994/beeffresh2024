@props([
    'count' => 0,
])

<a
    href="{{ route('carrito.ver') }}"
    data-bf-cart-link
    {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center rounded-lg hover:bg-white/10']) }}
    aria-label="Carrito{{ (int) $count > 0 ? ', '.(int) $count.' productos' : '' }}"
>
    <span class="text-[1.4rem] leading-none select-none" aria-hidden="true">🛒</span>
    <x-store.cart-count-badge :count="$count" />
</a>
