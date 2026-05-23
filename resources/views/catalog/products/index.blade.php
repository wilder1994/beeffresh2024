@extends('catalog.layout')

@section('catalogTitle', 'Productos · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Productos</h1>
            <p class="text-sm text-gray-600">Gestión comercial del catálogo de carnicería.</p>
        </div>
        <a href="{{ route('catalog.products.create') }}" class="bf-btn-primary shrink-0">Nuevo producto</a>
    </div>

    <form method="GET" class="bf-form-toolbar mb-4 flex flex-wrap gap-2 items-end">
        <div class="flex-1 min-w-[12rem]">
            <label class="bf-label-muted" for="q">Buscar</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" class="bf-input" placeholder="Nombre o SKU">
        </div>
        <div class="min-w-[10rem]">
            <label class="bf-label-muted" for="meat_type_id">Tipo</label>
            <select id="meat_type_id" name="meat_type_id" class="bf-select">
                <option value="">Todos</option>
                @foreach($meatTypes as $type)
                    <option value="{{ $type->id }}" @selected((int) request('meat_type_id') === $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bf-btn-primary">Filtrar</button>
    </form>

    @if($products->isEmpty())
        <p class="text-gray-600 bg-white rounded-xl border border-amber-100 p-6 text-center">No hay productos. Crea el primero con «Nuevo producto».</p>
    @else
        <div class="bf-table-panel">
            <table class="bf-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Tipo / Corte</th>
                        <th>Precio kg</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-stone-200">
                                    @endif
                                    <span class="font-medium">{{ $product->name }}</span>
                                    @if($product->featured)
                                        <span class="text-[10px] uppercase tracking-wide text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Destacado</span>
                                    @endif
                                </div>
                            </td>
                            <td class="font-mono text-xs">{{ $product->sku }}</td>
                            <td class="text-sm text-gray-600">{{ $product->meatType?->name }} · {{ $product->meatCut?->name }}</td>
                            <td class="tabular-nums">
                                @if($product->isOnPromotion())
                                    <span class="line-through text-gray-400">${{ number_format((float) $product->price_per_kg, 0, ',', '.') }}</span>
                                    <span class="text-emerald-700 font-semibold">${{ number_format($product->effectivePriceKg(), 0, ',', '.') }}</span>
                                @else
                                    ${{ number_format((float) $product->price_per_kg, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="tabular-nums @if($product->isLowStock()) text-red-700 font-semibold @endif">
                                {{ number_format((float) $product->stock, 1, ',', '.') }} {{ $product->stock_unit->value }}
                            </td>
                            <td><span class="text-xs">{{ $product->status->label() }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('catalog.products.edit', $product) }}" class="text-sm text-[var(--bf-brand)] hover:underline mr-2">Editar</a>
                                <x-bf.delete-action
                                    :action="route('catalog.products.destroy', $product)"
                                    :block-when-count="$product->order_items_count"
                                    blocked-message="No se puede eliminar este producto porque aparece en uno o más pedidos."
                                    confirm-title="¿Eliminar producto?"
                                    :confirm-message="'Se eliminará «'.$product->name.'».'"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
@endsection
