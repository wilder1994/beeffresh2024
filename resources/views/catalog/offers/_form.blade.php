@props(['offer' => null, 'products', 'defaultType' => null, 'lockType' => false])

@php
    use App\Domain\Store\OfferType;
    $isEdit = $offer !== null;
    $resolvedDefaultType = $defaultType instanceof OfferType
        ? $defaultType
        : ($defaultType !== null ? OfferType::from($defaultType) : OfferType::Bundle);

    $rawItems = old('items');
    if (! is_array($rawItems) || $rawItems === []) {
        $rawItems = $offer?->items?->map(fn ($i) => [
            'product_id' => $i->product_id,
            'quantity' => $i->quantity,
            'sale_unit' => $i->sale_unit?->value ?? 'kg',
        ])->values()->all() ?? [
            ['product_id' => '', 'quantity' => 1, 'sale_unit' => 'kg'],
            ['product_id' => '', 'quantity' => 1, 'sale_unit' => 'kg'],
        ];
    }

    $nextItemId = 1;
    $bundleItems = collect($rawItems)->values()->map(function (array $item) use (&$nextItemId): array {
        return [
            '_id' => $nextItemId++,
            'product_id' => (string) ($item['product_id'] ?? ''),
            'quantity' => $item['quantity'] ?? 1,
            'sale_unit' => $item['sale_unit'] ?? 'kg',
        ];
    })->all();

    $bundleItemSeq = $nextItemId;

    $productPrices = $products->mapWithKeys(fn ($product) => [
        (string) $product->id => [
            'price_kg' => (float) $product->price_per_kg,
            'price_lb' => (float) $product->price_per_lb,
            'promo_kg' => $product->isOnPromotion() ? (float) $product->effectivePriceKg() : null,
            'promo_lb' => $product->isOnPromotion() ? (float) $product->effectivePriceLb() : null,
            'on_promo' => $product->isOnPromotion(),
        ],
    ])->all();

    $volumeSaleUnit = old('volume_sale_unit', $offer?->volume_sale_unit?->value ?? 'lb');
    $volumeMinQty = old('volume_min_quantity', $offer?->volume_min_quantity ?? ($volumeSaleUnit === 'lb' ? 3 : 1.5));
    $volumeProductId = (string) old('product_id', $offer?->product_id ?? '');
    $volumeOfferUnitPrice = old('volume_offer_unit_price');
    if ($volumeOfferUnitPrice === null && $offer !== null) {
        $volumeOfferUnitPrice = $volumeSaleUnit === 'lb'
            ? $offer->volume_offer_price_lb
            : $offer->volume_offer_price_kg;
    }
    if ($volumeOfferUnitPrice === null) {
        $volumeOfferUnitPrice = old('volume_offer_price_lb') ?? old('volume_offer_price_kg') ?? '';
    }
@endphp

<div
    class="space-y-4"
    x-data="{
        type: @js(old('type', $offer?->type?->value ?? $resolvedDefaultType->value)),
        items: @js($bundleItems),
        itemSeq: @js($bundleItemSeq),
        productPrices: @js($productPrices),
        offerPrice: @js(old('offer_price', $offer?->offer_price ?? '')),
        addItem() {
            this.items = [
                ...this.items,
                {
                    _id: this.itemSeq++,
                    product_id: '',
                    quantity: 1,
                    sale_unit: 'kg',
                },
            ];
        },
        removeItem(id) {
            if (this.items.length <= 2) {
                return;
            }
            this.items = this.items.filter((item) => item._id !== id);
        },
        lineReference(item) {
            if (! item.product_id) {
                return 0;
            }
            const prices = this.productPrices[item.product_id];
            if (! prices) {
                return 0;
            }
            const qty = Number(item.quantity) || 0;
            if (qty <= 0) {
                return 0;
            }
            const unitPrice = item.sale_unit === 'lb' ? Number(prices.price_lb) : Number(prices.price_kg);
            return Math.round(qty * unitPrice);
        },
        referenceTotal() {
            return this.items.reduce((sum, item) => sum + this.lineReference(item), 0);
        },
        formatMoney(value) {
            return Math.round(Number(value)).toLocaleString('es-CO');
        },
        get packSavings() {
            const reference = this.referenceTotal();
            const offer = Number(this.offerPrice) || 0;
            if (reference > 0 && offer > 0 && offer < reference) {
                return reference - offer;
            }
            return 0;
        },
        volumeProductId: @js($volumeProductId),
        volumeMinQty: @js($volumeMinQty),
        volumeUnit: @js($volumeSaleUnit),
        volumeOfferPrice: @js($volumeOfferUnitPrice),
        volumeMinQuantityMin() {
            return this.volumeUnit === 'lb' ? 3 : 1.5;
        },
        volumeUnitSuffix() {
            return this.volumeUnit === 'lb' ? 'lb' : 'kg';
        },
        volumeCatalogUnitPrice() {
            if (! this.volumeProductId) {
                return 0;
            }
            const prices = this.productPrices[this.volumeProductId];
            if (! prices) {
                return 0;
            }
            if (this.volumeUnit === 'lb') {
                return prices.on_promo && prices.promo_lb != null
                    ? Number(prices.promo_lb)
                    : Number(prices.price_lb);
            }
            return prices.on_promo && prices.promo_kg != null
                ? Number(prices.promo_kg)
                : Number(prices.price_kg);
        },
        volumeReferenceTotal() {
            const qty = Number(this.volumeMinQty) || 0;
            if (qty <= 0) {
                return 0;
            }
            return Math.round(qty * this.volumeCatalogUnitPrice());
        },
        volumeOfferTotal() {
            const qty = Number(this.volumeMinQty) || 0;
            const price = Number(this.volumeOfferPrice) || 0;
            if (qty <= 0 || price <= 0) {
                return 0;
            }
            return Math.round(qty * price);
        },
        get volumeSavings() {
            const reference = this.volumeReferenceTotal();
            const offer = this.volumeOfferTotal();
            if (reference > 0 && offer > 0 && offer < reference) {
                return reference - offer;
            }
            return 0;
        },
    }"
>
    <div class="bf-offer-form-head">
        <div class="bf-offer-form-head__fields">
            <div>
                <label class="bf-label" for="offer-type">Tipo</label>
                @if($lockType || $isEdit)
                    <input type="hidden" name="type" value="{{ old('type', $offer?->type?->value ?? $resolvedDefaultType->value) }}">
                    <p id="offer-type" class="bf-input bg-stone-50 text-gray-800 cursor-default">
                        {{ ($offer?->type ?? $resolvedDefaultType)->label() }}
                    </p>
                @else
                    <select id="offer-type" name="type" class="bf-select" x-model="type" required>
                        @foreach(OfferType::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="bf-label" for="offer-name">Nombre</label>
                <input id="offer-name" type="text" name="name" class="bf-input" value="{{ old('name', $offer?->name) }}" required>
            </div>

            <div class="bf-offer-form-head__description">
                <label class="bf-label" for="offer-description">Descripción</label>
                <textarea
                    id="offer-description"
                    name="description"
                    class="bf-textarea bf-offer-form-head__textarea"
                    rows="3"
                    x-bind:disabled="type === '{{ OfferType::Volume->value }}'"
                    x-bind:placeholder="type === '{{ OfferType::Volume->value }}' ? 'Se genera automáticamente según cantidad y unidad mínima.' : ''"
                >{{ old('description', $offer?->description) }}</textarea>
                <p class="text-[11px] text-[var(--bf-muted)] mt-1" x-show="type === '{{ OfferType::Volume->value }}'" x-cloak>
                    En ofertas por cantidad, la descripción pública se genera al guardar según la unidad y cantidad mínima.
                </p>
            </div>
        </div>

        <div class="bf-offer-form-head__media">
            <x-bf.image-upload-zone
                input-id="offer-image-upload"
                name="image"
                :current-url="$offer?->imageUrl()"
                crop-profile="catalog"
                :show-hint="false"
                class="bf-offer-form-head__upload"
            />
            @unless($isEdit)
                <p class="text-[11px] text-[var(--bf-muted)] mt-1.5 shrink-0">Imagen obligatoria · 4:3 · se ajusta en el editor</p>
            @endunless
        </div>
    </div>

    <div
        x-show="type === '{{ OfferType::Bundle->value }}'"
        x-cloak
        class="space-y-3 bf-form-section--nested bf-form-section p-4"
    >
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-[var(--bf-ink)]">Productos del pack</h3>
            <button type="button" class="bf-btn-ghost text-xs" @click="addItem()">+ Añadir línea</button>
        </div>

        <div class="bf-offer-pack-table">
            <div class="bf-offer-pack-table__inner">
                <div class="bf-offer-pack-row bf-offer-pack-row--head">
                    <span>Producto</span>
                    <span>Cantidad</span>
                    <span>Unidad</span>
                    <span class="bf-offer-pack-col-ref">Valor real</span>
                    <span></span>
                </div>

                <div class="bf-offer-pack-rows">
                    <template x-for="(item, index) in items" :key="item._id">
                        <div class="bf-offer-pack-row">
                            <div>
                                <label class="sr-only" x-bind:for="'offer-item-product-' + item._id">Producto</label>
                                <select
                                    class="bf-select w-full"
                                    x-bind:id="'offer-item-product-' + item._id"
                                    x-bind:name="type === '{{ OfferType::Bundle->value }}' ? 'items[' + index + '][product_id]' : null"
                                    x-bind:disabled="type !== '{{ OfferType::Bundle->value }}'"
                                    x-model="item.product_id"
                                    required
                                >
                                    <option value="">Seleccionar…</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="sr-only" x-bind:for="'offer-item-qty-' + item._id">Cantidad</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="bf-input w-full"
                                    x-bind:id="'offer-item-qty-' + item._id"
                                    x-bind:name="type === '{{ OfferType::Bundle->value }}' ? 'items[' + index + '][quantity]' : null"
                                    x-bind:disabled="type !== '{{ OfferType::Bundle->value }}'"
                                    x-model.number="item.quantity"
                                    required
                                >
                            </div>
                            <div>
                                <label class="sr-only" x-bind:for="'offer-item-unit-' + item._id">Unidad</label>
                                <select
                                    class="bf-select w-full"
                                    x-bind:id="'offer-item-unit-' + item._id"
                                    x-bind:name="type === '{{ OfferType::Bundle->value }}' ? 'items[' + index + '][sale_unit]' : null"
                                    x-bind:disabled="type !== '{{ OfferType::Bundle->value }}'"
                                    x-model="item.sale_unit"
                                >
                                    <option value="kg">kg</option>
                                    <option value="lb">lb</option>
                                </select>
                            </div>
                            <div class="bf-offer-pack-col-ref">
                                <div
                                    class="bf-offer-pack-ref"
                                    x-text="lineReference(item) > 0 ? '$' + formatMoney(lineReference(item)) : '—'"
                                    aria-label="Valor real"
                                ></div>
                            </div>
                            <div class="flex justify-end pb-1">
                                <button
                                    type="button"
                                    class="bf-btn-ghost text-xs whitespace-nowrap"
                                    :class="{ 'hidden': items.length <= 2 }"
                                    :disabled="items.length <= 2"
                                    @click.stop="removeItem(item._id)"
                                >
                                    Quitar
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="bf-offer-pack-pricing pt-2 border-t border-[var(--bf-border-brand-subtle)]">
            <div class="bf-offer-pack-pricing__row bf-offer-pack-pricing__row--head">
                <span>Valor real del pack</span>
                <span>Precio del pack (COP)</span>
                <span>Ahorro vs. valor real</span>
            </div>
            <div class="bf-offer-pack-pricing__row">
                <div>
                    <label class="sr-only" for="offer-reference-total">Valor real del pack</label>
                    <input
                        id="offer-reference-total"
                        type="text"
                        class="bf-input w-full tabular-nums"
                        readonly
                        tabindex="-1"
                        x-bind:value="'$' + formatMoney(referenceTotal())"
                    >
                </div>
                <div>
                    <label class="sr-only" for="offer-price">Precio del pack (COP)</label>
                    <input
                        id="offer-price"
                        type="number"
                        step="1"
                        min="0"
                        name="offer_price"
                        class="bf-input w-full tabular-nums"
                        x-model="offerPrice"
                        x-bind:disabled="type !== '{{ OfferType::Bundle->value }}'"
                    >
                </div>
                <div>
                    <label class="sr-only" for="offer-pack-savings">Ahorro vs. valor real</label>
                    <input
                        id="offer-pack-savings"
                        type="text"
                        class="bf-input w-full tabular-nums"
                        readonly
                        tabindex="-1"
                        x-bind:value="packSavings > 0 ? '$' + formatMoney(packSavings) : '—'"
                    >
                </div>
            </div>
            <p class="text-[11px] text-[var(--bf-muted)] mt-2 leading-relaxed">
                Valor real = suma al precio de catálogo (sin promos del producto). Precio del pack = lo que verá el cliente. Ahorro = diferencia cuando el precio del pack es menor al valor real.
            </p>
        </div>
    </div>

    <div
        x-show="type === '{{ OfferType::Volume->value }}'"
        x-cloak
        class="space-y-3 bf-form-section--nested bf-form-section p-4"
    >
        <h3 class="text-sm font-semibold text-[var(--bf-ink)]">Oferta por cantidad</h3>

        <div class="bf-offer-volume-table">
            <div class="bf-offer-volume-table__inner">
                <div class="bf-offer-volume-row bf-offer-volume-row--head">
                    <span>Producto</span>
                    <span>Cantidad mínima</span>
                    <span>Unidad mínima</span>
                </div>
                <div class="bf-offer-volume-row">
                    <div>
                        <label class="sr-only" for="volume-product">Producto</label>
                        <select
                            id="volume-product"
                            class="bf-select w-full"
                            x-model="volumeProductId"
                            x-bind:name="type === '{{ OfferType::Volume->value }}' ? 'product_id' : null"
                            x-bind:disabled="type !== '{{ OfferType::Volume->value }}'"
                            required
                        >
                            <option value="">Seleccionar…</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sr-only" for="volume-min">Cantidad mínima</label>
                        <input
                            id="volume-min"
                            type="number"
                            step="0.01"
                            class="bf-input w-full"
                            x-model.number="volumeMinQty"
                            x-bind:min="volumeMinQuantityMin()"
                            x-bind:name="type === '{{ OfferType::Volume->value }}' ? 'volume_min_quantity' : null"
                            x-bind:disabled="type !== '{{ OfferType::Volume->value }}'"
                            required
                        >
                    </div>
                    <div>
                        <label class="sr-only" for="volume-unit">Unidad mínima</label>
                        <select
                            id="volume-unit"
                            class="bf-select w-full"
                            x-model="volumeUnit"
                            x-bind:name="type === '{{ OfferType::Volume->value }}' ? 'volume_sale_unit' : null"
                            x-bind:disabled="type !== '{{ OfferType::Volume->value }}'"
                        >
                            <option value="kg">kg</option>
                            <option value="lb">lb</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="bf-offer-pack-pricing bf-offer-volume-pricing pt-2 border-t border-[var(--bf-border-brand-subtle)]">
            <div class="bf-offer-pack-pricing__row bf-offer-pack-pricing__row--head">
                <span x-text="'Valor real / ' + volumeUnitSuffix()"></span>
                <span x-text="'Precio oferta / ' + volumeUnitSuffix()"></span>
                <span>Ahorro vs. valor real</span>
            </div>
            <div class="bf-offer-pack-pricing__row">
                <div>
                    <label class="sr-only" x-bind:for="'volume-reference-' + volumeUnitSuffix()">Valor real</label>
                    <input
                        type="text"
                        class="bf-input w-full tabular-nums"
                        readonly
                        tabindex="-1"
                        x-bind:value="volumeCatalogUnitPrice() > 0 ? '$' + formatMoney(volumeCatalogUnitPrice()) : '—'"
                    >
                </div>
                <div>
                    <label class="sr-only" x-bind:for="'volume-offer-price-' + volumeUnitSuffix()">Precio oferta</label>
                    <input
                        id="volume-offer-unit-price"
                        type="number"
                        step="1"
                        min="0"
                        class="bf-input w-full tabular-nums"
                        x-model="volumeOfferPrice"
                        x-bind:name="type === '{{ OfferType::Volume->value }}' ? 'volume_offer_unit_price' : null"
                        x-bind:disabled="type !== '{{ OfferType::Volume->value }}'"
                    >
                </div>
                <div>
                    <label class="sr-only" for="volume-savings">Ahorro vs. valor real</label>
                    <input
                        id="volume-savings"
                        type="text"
                        class="bf-input w-full tabular-nums"
                        readonly
                        tabindex="-1"
                        x-bind:value="volumeSavings > 0 ? '$' + formatMoney(volumeSavings) : '—'"
                    >
                </div>
            </div>
            <p class="text-[11px] text-[var(--bf-muted)] mt-2 leading-relaxed">
                Mínimo 3 lb (3 lb o 1,5 kg). El precio por escala debe ser menor que la promoción individual activa o, si no hay promo, que el precio de catálogo. Ahorro = diferencia al comprar la cantidad mínima.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="bf-label" for="offer-sort">Orden</label>
            <input id="offer-sort" type="number" min="0" name="sort_order" class="bf-input" value="{{ old('sort_order', $offer?->sort_order ?? 0) }}">
        </div>
        <div class="flex flex-col gap-2 pt-6">
            <label class="bf-form-check-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer?->is_active ?? true))> Activo</label>
            <label class="bf-form-check-item"><input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $offer?->show_on_home ?? true))> Mostrar en inicio</label>
            <label class="bf-form-check-item"><input type="checkbox" name="show_on_cinta" value="1" @checked(old('show_on_cinta', $offer?->show_on_cinta))> Mostrar en cinta</label>
        </div>
    </div>
</div>
