@extends('catalog.layout')

@section('catalogTitle', 'Inventario · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Inventario</h1>
            <p class="text-sm text-gray-600">Stock actual y umbrales de alerta.</p>
        </div>
        <a href="{{ route('catalog.inventory.index', ['low_only' => request('low_only') ? null : 1]) }}" class="bf-btn-ghost">
            {{ request('low_only') ? 'Ver todo' : 'Solo stock bajo' }}
        </a>
    </div>

    <form method="POST" action="{{ route('catalog.inventory.update') }}" class="space-y-4">
        @csrf
        @method('PUT')
        @if(request('low_only'))
            <input type="hidden" name="low_only" value="1">
        @endif
        <div class="bf-table-panel">
            <table class="bf-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Alerta mín.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                        <tr @class(['bg-red-50/60' => $product->isLowStock()])>
                            <td>
                                {{ $product->name }}
                                <span class="text-xs text-gray-500">({{ $product->stock_unit->value }})</span>
                                <input type="hidden" name="stock[{{ $index }}][id]" value="{{ $product->id }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="stock[{{ $index }}][stock]" class="bf-input max-w-[8rem]" value="{{ $product->stock }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="stock[{{ $index }}][min_stock]" class="bf-input max-w-[8rem]" value="{{ $product->min_stock }}" required>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bf-form-actions">
            <button type="submit" class="bf-btn-primary">Guardar inventario</button>
        </div>
    </form>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
