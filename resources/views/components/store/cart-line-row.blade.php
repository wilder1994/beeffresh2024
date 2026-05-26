@props(['linea'])

@php
    $unitLabel = $linea['sale_unit'] instanceof \App\Domain\Catalog\StockUnit
        ? $linea['sale_unit']->value
        : (string) $linea['sale_unit'];
    $qty = (float) $linea['cantidad'];
    $isPack = $unitLabel === 'pack';
    $tier = $linea['pricing_tier'] ?? null;
    $unitBadge = $isPack ? 'PACK' : strtoupper($unitLabel);
@endphp

<article class="bf-cart-table__row"
    data-cart-line
    data-line-key="{{ $linea['line_key'] }}"
    data-cart-line-type="{{ $linea['tipo'] ?? 'product' }}"
    @if(($linea['tipo'] ?? '') === 'product' && isset($linea['product_id']))
        data-product-id="{{ $linea['product_id'] }}"
    @endif
    @if(($linea['tipo'] ?? '') === 'offer')
        data-offer-id="{{ $linea['line_key'] }}"
    @endif
>
    {{-- Producto --}}
    <div class="bf-cart-table__cell bf-cart-table__cell--product">
        <div class="bf-cart-product">
            @if($linea['imagen_url'])
                <div class="bf-cart-product__media shrink-0">
                    <img src="{{ $linea['imagen_url'] }}" alt="{{ $linea['nombre'] }}" class="bf-cart-product__img" loading="lazy">
                </div>
            @else
                <div class="bf-cart-product__media bf-cart-product__media--empty shrink-0" aria-hidden="true">
                    <span>Sin imagen</span>
                </div>
            @endif

            <div class="bf-cart-product__body min-w-0">
                <p class="hidden text-sm text-red-700 font-medium mt-1" data-cart-line-invalid-msg role="status"></p>
                <div class="bf-cart-product__title-row">
                    <h2 class="bf-cart-product__title">{{ $linea['nombre'] }}</h2>
                    <span class="bf-cart-unit-badge">{{ $unitBadge }}</span>
                </div>

                @if($tier === 'volume')
                    <span class="bf-cart-promo-badge bf-cart-promo-badge--volume">Oferta por volumen</span>
                @elseif($tier === 'promo')
                    <span class="bf-cart-promo-badge">Promoción activa</span>
                @endif

                <p class="bf-cart-product__meta tabular-nums md:hidden">
                    @if($isPack)
                        ${{ number_format($linea['precio'], 0, ',', '.') }}/pack
                    @else
                        ${{ number_format($linea['precio'], 0, ',', '.') }}/{{ $unitLabel }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Precio unitario --}}
    <div class="bf-cart-table__cell bf-cart-table__cell--price hidden md:block">
        <span class="bf-cart-price-unit tabular-nums">
            ${{ number_format($linea['precio'], 0, ',', '.') }}
        </span>
        <span class="bf-cart-price-suffix">/{{ $isPack ? 'pack' : $unitLabel }}</span>
    </div>

    {{-- Cantidad --}}
    <div class="bf-cart-table__cell bf-cart-table__cell--qty">
        <span class="bf-cart-table__mobile-label md:hidden">Cantidad</span>
        <x-store.cart-line-qty
            :line-key="$linea['line_key']"
            :cantidad="$linea['cantidad']"
            :unit-label="$unitLabel"
            :is-pack="$isPack"
        />
    </div>

    {{-- Subtotal + eliminar --}}
    <div class="bf-cart-table__cell bf-cart-table__cell--total">
        <div class="bf-cart-total-cell">
            <div class="bf-cart-total-cell__amount">
                <span class="bf-cart-table__mobile-label md:hidden">Subtotal</span>
                <span class="bf-cart-subtotal tabular-nums">${{ number_format($linea['subtotal'], 0, ',', '.') }}</span>
            </div>
            <x-store.cart-line-remove :line-key="$linea['line_key']" />
        </div>
    </div>
</article>
