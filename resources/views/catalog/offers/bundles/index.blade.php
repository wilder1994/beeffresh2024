@extends('catalog.layout')

@section('catalogTitle', 'Combos y packs · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-900">Combos y packs</h1>
            <p class="text-sm text-gray-600 mt-0.5">Packs multi-producto con precio fijo para la tienda pública.</p>
        </div>
        <a href="{{ route('catalog.offers.bundles.create') }}" class="bf-btn-primary shrink-0">Nuevo combo</a>
    </div>

    @if($rows !== [])
        @include('catalog.offers.partials.stats-bar', [
            'total' => $stats['total'],
            'active' => $stats['active'],
            'inactive' => $stats['inactive'],
            'lowStock' => $stats['low_stock'],
        ])
    @endif

    @if($rows === [])
        <div class="bf-catalog-empty">
            <p class="bf-catalog-empty__title">Aún no hay combos ni packs</p>
            <p class="bf-catalog-empty__text">Crea un pack con varios productos y un precio cerrado para venderlo en la tienda.</p>
            <a href="{{ route('catalog.offers.bundles.create') }}" class="bf-btn-primary mt-4 inline-flex">Crear primer combo</a>
        </div>
    @else
        <div class="bf-table-panel mt-4">
            <table class="bf-table bf-table--sticky-head bf-table--catalog-offers">
                <thead>
                    <tr>
                        <th class="bf-catalog-th--primary">Pack</th>
                        <th class="bf-catalog-th--num">Precios</th>
                        <th class="bf-catalog-th--num">Ahorro</th>
                        <th class="bf-catalog-th--num">Disponibles</th>
                        <th class="bf-catalog-th--center">Estado</th>
                        <th class="bf-catalog-th--actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $offer = $row['offer'];
                            $itemsLabel = $row['items_count'].' '.($row['items_count'] === 1 ? 'producto' : 'productos');
                        @endphp
                        <tr>
                            <td>
                                <div
                                    class="bf-offer-cell"
                                    title="{{ $offer->name }} · {{ $itemsLabel }}"
                                >
                                    <img src="{{ $row['image_url'] }}" alt="" class="bf-offer-cell__thumb" loading="lazy">
                                    <p class="bf-offer-cell__title">{{ $offer->name }}</p>
                                </div>
                            </td>
                            <td class="bf-catalog-num">
                                <div class="bf-catalog-price-stack">
                                    <span class="bf-catalog-price-stack__ref tabular-nums">${{ number_format($row['reference'], 0, ',', '.') }}</span>
                                    <span class="bf-catalog-price-stack__main tabular-nums">${{ number_format($row['offer_total'], 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="bf-catalog-num">
                                @if($row['discount_percent'] !== null)
                                    <span class="bf-catalog-savings">−{{ $row['discount_percent'] }}%</span>
                                @else
                                    <span class="bf-catalog-muted-dash">—</span>
                                @endif
                            </td>
                            <td class="bf-catalog-num">
                                @include('catalog.offers.partials.stock-cell', [
                                    'status' => $row['stock_status'],
                                    'value' => $row['available'],
                                ])
                            </td>
                            <td class="bf-catalog-center">
                                @include('catalog.offers.partials.status-pill', ['active' => $offer->is_active])
                            </td>
                            @include('catalog.offers.partials.row-actions', ['offer' => $offer])
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
