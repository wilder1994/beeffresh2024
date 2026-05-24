@props([
    'lineKey',
    'cantidad',
    'unitLabel' => 'kg',
    'isPack' => false,
])

@php
    $qty = $isPack ? max(1, (int) $cantidad) : max(1.0, (float) $cantidad);
    $qtyInt = (int) floor($qty);
    $inputValue = $isPack
        ? (string) $qtyInt
        : (fmod($qty, 1.0) === 0.0 ? (string) (int) $qty : rtrim(rtrim(number_format($qty, 1, '.', ''), '0'), '.'));
@endphp

<div class="bf-cart-qty-wrap">
    <div class="bf-cart-qty" role="group" aria-label="Cantidad">
        <form method="POST" action="{{ route('carrito.linea.actualizar') }}" class="bf-cart-qty__form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="line_key" value="{{ $lineKey }}">
            <input type="hidden" name="cantidad" value="{{ max(1, $qtyInt - 1) }}">
            <button type="submit" class="bf-cart-qty__btn" aria-label="Disminuir cantidad" @disabled($qtyInt <= 1)>−</button>
        </form>

        <form method="POST" action="{{ route('carrito.linea.actualizar') }}" class="bf-cart-qty__form bf-cart-qty__form--input">
            @csrf
            @method('PATCH')
            <input type="hidden" name="line_key" value="{{ $lineKey }}">
            <input
                type="number"
                name="cantidad"
                value="{{ $inputValue }}"
                min="1"
                step="1"
                class="bf-cart-qty__input"
                aria-label="Cantidad"
                onchange="this.form.submit()"
            >
        </form>

        <form method="POST" action="{{ route('carrito.linea.actualizar') }}" class="bf-cart-qty__form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="line_key" value="{{ $lineKey }}">
            <input type="hidden" name="cantidad" value="{{ $qtyInt + 1 }}">
            <button type="submit" class="bf-cart-qty__btn" aria-label="Aumentar cantidad">+</button>
        </form>
    </div>
    @unless($isPack)
        <span class="bf-cart-qty__unit">{{ $unitLabel }}</span>
    @else
        <span class="bf-cart-qty__unit">{{ $qtyInt === 1 ? 'pack' : 'packs' }}</span>
    @endunless
</div>
