@extends('catalog.layout')

@section('catalogTitle', 'Escalas por volumen · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-900">Escalas por volumen</h1>
            <p class="text-sm text-gray-600 mt-0.5">Precio unitario especial al alcanzar la cantidad mínima en ficha y carrito.</p>
        </div>
        <a href="{{ route('catalog.offers.volumes.create') }}" class="bf-btn-primary shrink-0">Nueva escala</a>
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
            <p class="bf-catalog-empty__title">Sin escalas por volumen</p>
            <p class="bf-catalog-empty__text">Vincula un producto del catálogo con cantidad mínima y precio por kg o lb.</p>
            <a href="{{ route('catalog.offers.volumes.create') }}" class="bf-btn-primary mt-4 inline-flex">Crear primera escala</a>
        </div>
    @else
        <div class="bf-table-panel mt-4">
            <table class="bf-table bf-table--sticky-head bf-table--catalog-offers">
                <thead>
                    <tr>
                        <th class="bf-catalog-th--primary">Producto</th>
                        <th class="bf-catalog-th--num">Condición</th>
                        <th class="bf-catalog-th--num">Precio escala</th>
                        <th class="bf-catalog-th--num">Vs. catálogo</th>
                        <th class="bf-catalog-th--num">Disponibles</th>
                        <th class="bf-catalog-th--center">Estado</th>
                        <th class="bf-catalog-th--actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php $offer = $row['offer']; @endphp
                        <tr>
                            <td>
                                <div class="bf-offer-cell" title="{{ $row['offer_name'] }}">
                                    @if($row['product_image_url'])
                                        <img src="{{ $row['product_image_url'] }}" alt="" class="bf-offer-cell__thumb" loading="lazy">
                                    @else
                                        <div class="bf-offer-cell__thumb bf-offer-cell__thumb--empty" aria-hidden="true"></div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="bf-offer-cell__title">{{ $row['product_name'] }}</p>
                                        @if($row['product_sku'])
                                            <p class="bf-offer-cell__meta font-mono">{{ $row['product_sku'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="bf-catalog-num">
                                <span class="bf-catalog-condition tabular-nums">{{ $row['min_condition'] }}</span>
                            </td>
                            <td class="bf-catalog-num">
                                <div class="bf-catalog-price-primary">
                                    <span class="bf-catalog-scale-price tabular-nums">
                                        ${{ number_format($row['scale_price'], 0, ',', '.') }}/{{ $row['primary_unit'] }}
                                    </span>
                                    @if($row['alternate_scale_price'] !== null)
                                        <span class="bf-catalog-alt-price tabular-nums">
                                            ${{ number_format($row['alternate_scale_price'], 0, ',', '.') }}/{{ $row['alternate_unit'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="bf-catalog-num">
                                <div class="bf-catalog-ref-stack">
                                    <span class="bf-catalog-ref-price tabular-nums">
                                        ${{ number_format($row['reference_price'], 0, ',', '.') }}/{{ $row['primary_unit'] }}
                                    </span>
                                    @if($row['reference_tier'] === 'promo' || $row['savings_percent'] !== null)
                                        <span class="bf-catalog-ref-meta">
                                            @if($row['reference_tier'] === 'promo')
                                                Promo
                                            @endif
                                            @if($row['reference_tier'] === 'promo' && $row['savings_percent'] !== null)
                                                ·
                                            @endif
                                            @if($row['savings_percent'] !== null)
                                                −{{ $row['savings_percent'] }}%
                                            @endif
                                        </span>
                                    @endif
                                </div>
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
