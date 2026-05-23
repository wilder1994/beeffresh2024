@extends('catalog.layout')

@section('catalogTitle', 'Promociones · Catálogo')

@section('catalog')
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">Promociones de productos</h1>
        <p class="text-sm text-gray-600">Productos con precio promocional configurado.</p>
    </div>

    <div class="bf-table-panel">
        <table class="bf-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio normal</th>
                    <th>Precio promo</th>
                    <th>Vigencia</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->name }} <span class="text-xs text-gray-500 font-mono">({{ $product->sku }})</span></td>
                        <td class="tabular-nums">${{ number_format((float) $product->price_per_kg, 0, ',', '.') }}/kg</td>
                        <td class="tabular-nums text-emerald-700 font-semibold">
                            @if($product->isOnPromotion())
                                ${{ number_format($product->effectivePriceKg(), 0, ',', '.') }}/kg
                            @else
                                <span class="text-gray-400 font-normal">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-600">
                            @if($product->promo_start && $product->promo_end)
                                {{ $product->promo_start->format('d/m/Y') }} – {{ $product->promo_end->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('catalog.products.edit', $product) }}" class="text-sm text-[var(--bf-brand)] hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-gray-500">Ningún producto con promoción.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
