@props(['product', 'canAdd' => true])

@php
    use App\Domain\Catalog\StockUnit;
    use App\Services\Catalog\CartSessionService;
    use App\Services\Store\VolumeScaleService;

    $volumeScale = app(VolumeScaleService::class);
    $cartSession = app(CartSessionService::class);
    $volumeConfig = $volumeScale->purchaseConfig($product);
    $onPromo = $product->isOnPromotion();
    $catalogKg = (float) $product->price_per_kg;
    $catalogLb = (float) $product->price_per_lb;
    $standardKg = $product->effectivePriceKg();
    $standardLb = $product->effectivePriceLb();
    $maxKg = $cartSession->maxPurchasableUnits($product, StockUnit::Kg);
    $maxLb = $cartSession->maxPurchasableUnits($product, StockUnit::Lb);

    $purchaseInitial = [
        'defaultUnit' => 'kg',
        'defaultQty' => 1,
        'volumeConfig' => $volumeConfig,
        'catalogKg' => $catalogKg,
        'catalogLb' => $catalogLb,
        'standardKg' => $standardKg,
        'standardLb' => $standardLb,
        'onPromo' => $onPromo,
        'maxKg' => $maxKg,
        'maxLb' => $maxLb,
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
                :max="maxUnits"
                x-model.number="qty"
                @change="clampQty()"
                @blur="clampQty()"
                class="bf-store-qty-input"
                data-cart-qty-input
            >
            <span class="text-xs text-[var(--bf-muted)] shrink-0 w-5" x-text="unitLabel"></span>
        </div>
    </div>

    <p class="text-xs text-[var(--bf-muted)]" x-show="maxUnits > 0">
        Disponibles: <span class="font-medium" x-text="maxUnits + ' ' + unitLabel"></span>
    </p>
    <p class="text-xs font-medium text-amber-800 leading-snug" x-show="maxUnits === 0" x-cloak x-text="unavailableMessage"></p>

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
            class="agregar-carrito w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-red-600"
            data-id="{{ $product->id }}"
            :disabled="maxUnits === 0"
        >
            Agregar al carrito
        </button>
    @else
        <p class="text-sm text-[var(--bf-muted)] hidden" data-store-unavailable-msg data-store-availability-label>Agotado</p>
    @endif
</div>
