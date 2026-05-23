@php
    use App\Domain\Catalog\SaleType;
    use App\Domain\Catalog\StockUnit;
    /** @var \App\Models\Product|null $product */
    $product = $product ?? null;
    $cutsJson = $meatCuts->groupBy('meat_type_id')->map(fn ($cuts) => $cuts->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values());
@endphp

<div
    class="grid grid-cols-1 xl:grid-cols-3 gap-6"
    x-data="{
        meatTypeId: '{{ old('meat_type_id', $product?->meat_type_id) }}',
        meatCutId: '{{ old('meat_cut_id', $product?->meat_cut_id) }}',
        priceKg: '{{ old('price_per_kg', $product?->price_per_kg ?? '') }}',
        priceLb: '{{ old('price_per_lb', $product?->price_per_lb ?? '') }}',
        promoKg: '{{ old('promo_price_kg', $product?->promo_price_kg ?? '') }}',
        promoLb: '{{ old('promo_price_lb', $product?->promo_price_lb ?? '') }}',
        syncLb: true,
        cutsByType: @js($cutsJson),
        get cuts() {
            return this.cutsByType[this.meatTypeId] ?? [];
        },
        syncLbFromKg() {
            if (!this.syncLb || this.priceKg === '') return;
            this.priceLb = (parseFloat(this.priceKg) / 2).toFixed(2);
        },
        syncPromoLbFromKg() {
            if (!this.syncLb || this.promoKg === '') return;
            this.promoLb = (parseFloat(this.promoKg) / 2).toFixed(2);
        },
        previewName: '{{ old('name', $product?->name ?? 'Nombre del producto') }}',
        previewSku: '{{ $product?->sku ?? 'SKU auto' }}',
    }"
>
    <div class="xl:col-span-2 space-y-4">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @if($method ?? false)
                @method($method)
            @endif

            @include('catalog.products._form-general', ['product' => $product])

            <section class="bf-form-panel space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Clasificación</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="bf-label" for="meat_type_id">Tipo de carne</label>
                        <select id="meat_type_id" name="meat_type_id" class="bf-select" required x-model="meatTypeId" x-on:change="meatCutId = ''">
                            <option value="">Seleccionar…</option>
                            @foreach($meatTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="bf-label" for="meat_cut_id">Corte</label>
                        <select id="meat_cut_id" name="meat_cut_id" class="bf-select" required x-model="meatCutId">
                            <option value="">Seleccionar…</option>
                            <template x-for="cut in cuts" :key="cut.id">
                                <option :value="cut.id" x-text="cut.name" :selected="String(cut.id) === String(meatCutId)"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <p class="text-xs text-gray-500">El SKU se genera automáticamente al crear (ej. RES-ESP-0001).</p>
            </section>

            <section class="bf-form-panel space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Precios</h2>
                    <label class="text-xs flex items-center gap-1.5">
                        <input type="checkbox" x-model="syncLb" class="checkbox checkbox-xs">
                        Sincronizar lb = kg ÷ 2
                    </label>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="bf-label" for="price_per_kg">Precio por kg (COP)</label>
                        <input id="price_per_kg" type="number" step="0.01" min="0" name="price_per_kg" class="bf-input" required x-model="priceKg" x-on:input="syncLbFromKg()">
                    </div>
                    <div>
                        <label class="bf-label" for="price_per_lb">Precio por lb (COP)</label>
                        <input id="price_per_lb" type="number" step="0.01" min="0" name="price_per_lb" class="bf-input" required x-model="priceLb">
                    </div>
                </div>
            </section>

            <section class="bf-form-panel space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Promoción</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="bf-label" for="promo_price_kg">Precio promo kg</label>
                        <input id="promo_price_kg" type="number" step="0.01" min="0" name="promo_price_kg" class="bf-input" x-model="promoKg" x-on:input="syncPromoLbFromKg()">
                    </div>
                    <div>
                        <label class="bf-label" for="promo_price_lb">Precio promo lb</label>
                        <input id="promo_price_lb" type="number" step="0.01" min="0" name="promo_price_lb" class="bf-input" x-model="promoLb">
                    </div>
                    <div>
                        <label class="bf-label" for="promo_start">Inicio</label>
                        <input id="promo_start" type="date" name="promo_start" class="bf-input" value="{{ old('promo_start', $product?->promo_start?->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="bf-label" for="promo_end">Fin</label>
                        <input id="promo_end" type="date" name="promo_end" class="bf-input" value="{{ old('promo_end', $product?->promo_end?->format('Y-m-d')) }}">
                    </div>
                </div>
            </section>

            <section class="bf-form-panel space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Inventario</h2>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="bf-label" for="stock">Stock</label>
                        <input id="stock" type="number" step="0.01" min="0" name="stock" class="bf-input" required value="{{ old('stock', $product?->stock ?? 0) }}">
                    </div>
                    <div>
                        <label class="bf-label" for="stock_unit">Unidad</label>
                        <select id="stock_unit" name="stock_unit" class="bf-select" required>
                            @foreach(StockUnit::cases() as $unit)
                                <option value="{{ $unit->value }}" @selected(old('stock_unit', $product?->stock_unit?->value ?? StockUnit::Kg->value) === $unit->value)>{{ $unit->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="bf-label" for="min_stock">Alerta mínima</label>
                        <input id="min_stock" type="number" step="0.01" min="0" name="min_stock" class="bf-input" required value="{{ old('min_stock', $product?->min_stock ?? 5) }}">
                    </div>
                </div>
            </section>

            <section class="bf-form-panel space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Tipo de venta</h2>
                <select name="sale_type" class="bf-select" required>
                    @foreach(SaleType::cases() as $saleType)
                        <option value="{{ $saleType->value }}" @selected(old('sale_type', $product?->sale_type?->value ?? SaleType::VariableWeight->value) === $saleType->value)>{{ $saleType->label() }}</option>
                    @endforeach
                </select>
            </section>

            <div class="bf-form-actions">
                <a href="{{ route('catalog.products.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">{{ $submitLabel }}</button>
            </div>
        </form>
    </div>

    <aside class="xl:col-span-1">
        <div class="bf-form-panel sticky top-24 space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Vista previa</h2>
            <div class="rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
                <p class="text-xs text-gray-500 font-mono" x-text="previewSku"></p>
                <h3 class="text-lg font-semibold text-gray-900 mt-1" x-text="previewName"></h3>
                <p class="text-red-700 font-bold mt-2 tabular-nums">
                    $<span x-text="priceKg ? Number(priceKg).toLocaleString('es-CO') : '0'"></span> / kg
                </p>
                <p class="text-sm text-gray-600 tabular-nums">
                    $<span x-text="priceLb ? Number(priceLb).toLocaleString('es-CO') : '0'"></span> / lb
                </p>
                <template x-if="promoKg">
                    <p class="text-sm text-emerald-700 font-medium mt-2">Promo: $<span x-text="Number(promoKg).toLocaleString('es-CO')"></span> / kg</p>
                </template>
            </div>
        </div>
    </aside>
</div>

@if($errors->any())
    <div class="mt-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
