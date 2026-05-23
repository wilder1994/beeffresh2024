@props(['product'])

@php
    $priceKg = $product->effectivePriceKg();
    $priceLb = $product->effectivePriceLb();
    $baseKg = (float) $product->price_per_kg;
    $baseLb = (float) $product->price_per_lb;
@endphp

<div
    class="mt-2 space-y-2"
    data-product-purchase
    x-data="{
        unit: 'kg',
        qty: 1,
        priceKg: {{ json_encode($priceKg) }},
        priceLb: {{ json_encode($priceLb) }},
        onPromo: @js($product->isOnPromotion()),
        baseKg: {{ json_encode($baseKg) }},
        baseLb: {{ json_encode($baseLb) }},
        get unitPrice() {
            return this.unit === 'kg' ? Number(this.priceKg) : Number(this.priceLb);
        },
        get unitLabel() {
            return this.unit === 'kg' ? 'kg' : 'lb';
        },
        formatMoney(value) {
            return Math.round(Number(value)).toLocaleString('es-CO');
        }
    }"
    :data-sale-unit="unit"
    :data-cart-qty="qty"
>
    <div class="flex items-center justify-between gap-2">
        <div class="bf-store-unit-toggle">
            <button
                type="button"
                :class="unit === 'kg' ? 'bg-[var(--bf-brand)] text-white' : 'text-[var(--bf-muted)] hover:text-[var(--bf-ink)]'"
                @click="unit = 'kg'"
            >
                Kg
            </button>
            <button
                type="button"
                :class="unit === 'lb' ? 'bg-[var(--bf-brand)] text-white' : 'text-[var(--bf-muted)] hover:text-[var(--bf-ink)]'"
                @click="unit = 'lb'"
            >
                Lb
            </button>
        </div>

        <div class="flex items-center justify-end gap-1.5 min-w-0">
            <label class="text-xs font-medium text-[var(--bf-muted)] shrink-0">Cantidad</label>
            <input
                type="number"
                min="1"
                step="1"
                x-model.number="qty"
                class="bf-store-qty-input"
                data-cart-qty-input
            >
            <span class="text-xs text-[var(--bf-muted)] shrink-0 w-5" x-text="unitLabel"></span>
        </div>
    </div>

    <div class="text-sm leading-tight">
        <template x-if="onPromo && unit === 'kg'">
            <p class="line-through text-gray-400 tabular-nums text-xs">$<span x-text="formatMoney(baseKg)"></span>/kg</p>
        </template>
        <template x-if="onPromo && unit === 'lb'">
            <p class="line-through text-gray-400 tabular-nums text-xs">$<span x-text="formatMoney(baseLb)"></span>/lb</p>
        </template>
        <p class="text-red-600 font-medium tabular-nums">
            $<span x-text="formatMoney(unitPrice)"></span>/<span x-text="unitLabel"></span>
        </p>
    </div>

    <button
        type="button"
        class="agregar-carrito w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium"
        data-id="{{ $product->id }}"
    >
        Agregar al carrito
    </button>
</div>
