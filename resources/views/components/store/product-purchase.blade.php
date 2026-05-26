@props(['product', 'canAdd' => true])

@php
    use App\Services\Store\VolumeScaleService;

    $volumeScale = app(VolumeScaleService::class);
    $volumeConfig = $volumeScale->purchaseConfig($product);
    $onPromo = $product->isOnPromotion();
    $catalogKg = (float) $product->price_per_kg;
    $catalogLb = (float) $product->price_per_lb;
    $standardKg = $product->effectivePriceKg();
    $standardLb = $product->effectivePriceLb();

    $purchaseInitial = [
        'defaultUnit' => 'kg',
        'defaultQty' => 1,
        'volumeConfig' => $volumeConfig,
        'catalogKg' => $catalogKg,
        'catalogLb' => $catalogLb,
        'standardKg' => $standardKg,
        'standardLb' => $standardLb,
        'onPromo' => $onPromo,
    ];
@endphp

<div
    class="mt-2 space-y-3"
    data-store-product-id="{{ $product->id }}"
    data-product-purchase
    x-data="productPurchase(@js($purchaseInitial))"
    :data-sale-unit="unit"
    :data-cart-qty="qty"
>
    @if($volumeConfig)
        <p class="text-xs font-medium text-[var(--bf-brand)] leading-snug" x-text="volumeConfig.summary"></p>
    @endif

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

    <div class="rounded-lg border border-[var(--bf-border-brand-subtle)] bg-white/70 px-3 py-2.5 space-y-1.5">
        <p
            class="text-xs font-semibold"
            :class="quote.volumeActive ? 'text-[var(--bf-brand)]' : 'text-[var(--bf-muted)]'"
            x-text="quote.pricingLabel"
        ></p>

        <div class="text-sm leading-tight">
            <template x-if="quote.showStrikethrough">
                <p class="line-through text-gray-400 tabular-nums text-xs">
                    $<span x-text="formatMoney(quote.strikethroughPrice)"></span>/<span x-text="unitLabel"></span>
                </p>
            </template>
            <p class="text-[var(--bf-brand)] font-bold tabular-nums text-base">
                $<span x-text="formatMoney(quote.unitPrice)"></span>/<span x-text="unitLabel"></span>
            </p>
        </div>

        <template x-if="quote.feedbackMessage">
            <p
                class="text-xs font-medium leading-snug"
                :class="quote.volumeActive ? 'text-emerald-700' : 'text-amber-800'"
                x-text="quote.feedbackMessage"
            ></p>
        </template>
    </div>

    @if($canAdd)
        <button
            type="button"
            class="agregar-carrito w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium"
            data-id="{{ $product->id }}"
        >
            Agregar al carrito
        </button>
    @else
        <p class="text-sm text-[var(--bf-muted)] hidden" data-store-unavailable-msg data-store-availability-label>Agotado</p>
    @endif
</div>
