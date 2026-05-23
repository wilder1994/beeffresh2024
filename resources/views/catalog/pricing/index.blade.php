@extends('catalog.layout')

@section('catalogTitle', 'Precios · Catálogo')

@section('catalog')
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">Precios</h1>
        <p class="text-sm text-gray-600">Edición rápida de precios por kilo y libra.</p>
    </div>

    <form method="POST" action="{{ route('catalog.pricing.update') }}" class="space-y-4">
        @csrf
        @method('PUT')
        <label class="bf-form-check-item">
            <input type="checkbox" name="sync_lb" value="1" checked>
            <span>Recalcular lb = kg ÷ 2 al guardar (si lb vacío)</span>
        </label>
        <div class="bf-table-panel">
            <table class="bf-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio kg</th>
                        <th>Precio lb</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                        <tr>
                            <td>
                                {{ $product->name }}
                                <input type="hidden" name="prices[{{ $index }}][id]" value="{{ $product->id }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="prices[{{ $index }}][price_per_kg]" class="bf-input max-w-[9rem]" value="{{ $product->price_per_kg }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="prices[{{ $index }}][price_per_lb]" class="bf-input max-w-[9rem]" value="{{ $product->price_per_lb }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bf-form-actions">
            <button type="submit" class="bf-btn-primary">Guardar precios</button>
        </div>
    </form>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
