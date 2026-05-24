@props(['offer' => null, 'products'])

@php
    use App\Domain\Store\OfferType;
    $isEdit = $offer !== null;

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
        ],
    ])->all();
@endphp

<div
    class="space-y-4"
    x-data="{
        type: @js(old('type', $offer?->type?->value ?? OfferType::Bundle->value)),
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
    }"
>
    <div>
        <label class="bf-label" for="offer-type">Tipo</label>
        <select id="offer-type" name="type" class="bf-select" x-model="type" required>
            @foreach(OfferType::cases() as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="bf-label" for="offer-name">Nombre</label>
        <input id="offer-name" type="text" name="name" class="bf-input" value="{{ old('name', $offer?->name) }}" required>
    </div>

    <div>
        <label class="bf-label" for="offer-description">Descripción</label>
        <textarea id="offer-description" name="description" class="bf-textarea" rows="3">{{ old('description', $offer?->description) }}</textarea>
    </div>

    <x-bf.image-upload-zone
        input-id="offer-image-upload"
        name="image"
        :current-url="$offer?->imageUrl()"
        :show-hint="false"
    />
    @unless($isEdit)
        <p class="text-[11px] text-[var(--bf-muted)] -mt-2">Imagen obligatoria · JPG, PNG o WebP</p>
    @endunless

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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-[var(--bf-border-brand-subtle)]">
            <div>
                <label class="bf-label" for="offer-reference-total">Valor real del pack</label>
                <input
                    id="offer-reference-total"
                    type="text"
                    class="bf-input mt-1 tabular-nums"
                    readonly
                    tabindex="-1"
                    x-bind:value="'$' + formatMoney(referenceTotal())"
                >
                <p class="text-[11px] text-[var(--bf-muted)] mt-1 leading-relaxed">
                    Suma al precio de catálogo (sin promos del producto). Se actualiza al cambiar productos, cantidades o unidades.
                </p>
            </div>
            <div>
                <label class="bf-label" for="offer-price">Precio del pack (COP)</label>
                <input
                    id="offer-price"
                    type="number"
                    step="1"
                    min="0"
                    name="offer_price"
                    class="bf-input mt-1"
                    x-model="offerPrice"
                    x-bind:disabled="type !== '{{ OfferType::Bundle->value }}'"
                >
                <p class="text-[11px] text-[var(--bf-muted)] mt-1">Precio final que verá el cliente en tienda.</p>
                <p
                    class="text-xs font-semibold text-[var(--bf-brand)] mt-2 tabular-nums"
                    x-show="packSavings > 0"
                    x-cloak
                >
                    Ahorro vs. valor real: $<span x-text="formatMoney(packSavings)"></span>
                </p>
            </div>
        </div>
    </div>

    <div
        x-show="type === '{{ OfferType::Volume->value }}'"
        x-cloak
        class="space-y-3 bf-form-section--nested bf-form-section p-4"
    >
        <div>
            <label class="bf-label" for="volume-product">Producto</label>
            <select
                id="volume-product"
                name="product_id"
                class="bf-select"
                x-bind:disabled="type !== '{{ OfferType::Volume->value }}'"
            >
                <option value="">Seleccionar…</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected((int) old('product_id', $offer?->product_id) === $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="bf-label" for="volume-min">Cantidad mínima</label>
                <input id="volume-min" type="number" step="0.01" min="0.01" name="volume_min_quantity" class="bf-input" value="{{ old('volume_min_quantity', $offer?->volume_min_quantity) }}" x-bind:disabled="type !== '{{ OfferType::Volume->value }}'">
            </div>
            <div>
                <label class="bf-label" for="volume-unit">Unidad mínima</label>
                <select id="volume-unit" name="volume_sale_unit" class="bf-select" x-bind:disabled="type !== '{{ OfferType::Volume->value }}'">
                    <option value="kg" @selected(old('volume_sale_unit', $offer?->volume_sale_unit) === 'kg')>kg</option>
                    <option value="lb" @selected(old('volume_sale_unit', $offer?->volume_sale_unit) === 'lb')>lb</option>
                </select>
            </div>
            <div>
                <label class="bf-label" for="volume-price-kg">Precio oferta / kg</label>
                <input id="volume-price-kg" type="number" step="1" min="0" name="volume_offer_price_kg" class="bf-input" value="{{ old('volume_offer_price_kg', $offer?->volume_offer_price_kg) }}" x-bind:disabled="type !== '{{ OfferType::Volume->value }}'">
            </div>
            <div>
                <label class="bf-label" for="volume-price-lb">Precio oferta / lb</label>
                <input id="volume-price-lb" type="number" step="1" min="0" name="volume_offer_price_lb" class="bf-input" value="{{ old('volume_offer_price_lb', $offer?->volume_offer_price_lb) }}" x-bind:disabled="type !== '{{ OfferType::Volume->value }}'">
            </div>
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
