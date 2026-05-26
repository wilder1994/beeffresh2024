@extends('layouts.app')

@section('titulo', 'Panel · BEEF FRESH')

@section('contenido')
    @php
        $fmtMoney = static fn (float $n): string => '$'.number_format($n, 0, ',', '.');
    @endphp

    <div class="max-w-7xl mx-auto -mt-1">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 sm:gap-4 mb-4 md:mb-6 px-0 sm:px-1">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs uppercase tracking-widest text-[var(--bf-red)] font-semibold">Operaciones</p>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 leading-tight">Panel de administración</h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-0.5 sm:mt-1">Vista consolidada de pedidos, ingresos y catálogo · {{ now()->translatedFormat('l j \d\e F') }}</p>
            </div>
            <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm bg-[var(--bf-red)] hover:brightness-110 text-white border-0 shrink-0 w-full sm:w-auto">
                Ir a pedidos
            </a>
        </div>

        @if(count($alerts) > 0)
            <div class="space-y-2 mb-6 px-1">
                @foreach($alerts as $alert)
                    <div @class([
                        'rounded-xl px-4 py-3 text-sm border flex items-start gap-2',
                        'bg-amber-50 border-amber-200 text-amber-900' => $alert['type'] === 'warning',
                        'bg-red-50 border-red-200 text-red-900' => $alert['type'] === 'danger',
                    ])>
                        <span class="font-semibold shrink-0">{{ $alert['type'] === 'warning' ? 'Atención' : 'Alerta' }}:</span>
                        <span>{{ $alert['message'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mb-6 mx-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 text-sm">
                Sin alertas críticas en este momento.
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-4 mb-4 md:mb-6">
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide leading-tight">Pedidos hoy</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 mt-0.5 sm:mt-1 tabular-nums">{{ $kpi['orders_today'] }}</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5" data-dashboard-metric data-metric-key="pending">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide leading-tight">Pendientes</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--bf-red)] mt-0.5 sm:mt-1 tabular-nums" data-dashboard-metric-value>{{ $kpi['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide leading-tight">Ingresos hoy</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mt-0.5 sm:mt-1 tabular-nums">{{ $fmtMoney($kpi['revenue_today']) }}</p>
                <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5 sm:mt-1">Solo pedidos pagados</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide leading-tight">Ingresos del mes</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mt-0.5 sm:mt-1 tabular-nums">{{ $fmtMoney($kpi['revenue_month']) }}</p>
                <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5 sm:mt-1">{{ $kpi['products_catalog'] }} productos en catálogo</p>
            </div>
        </div>

        @php $nm = $notification_metrics ?? ['sent' => 0, 'failed' => 0, 'pending' => 0, 'avg_seconds' => null, 'by_channel' => []]; @endphp
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-2 sm:gap-3 md:gap-4 mb-4 md:mb-6">
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">Notif. enviadas (7d)</p>
                <p class="text-xl sm:text-2xl font-bold text-emerald-700 mt-1 tabular-nums">{{ $nm['sent'] }}</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">Fallidas / omitidas</p>
                <p class="text-xl sm:text-2xl font-bold text-red-600 mt-1 tabular-nums">{{ $nm['failed'] }}</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">Pendientes cola</p>
                <p class="text-xl sm:text-2xl font-bold text-amber-700 mt-1 tabular-nums">{{ $nm['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">Tiempo prom. envío</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ $nm['avg_seconds'] !== null ? $nm['avg_seconds'].'s' : '—' }}</p>
            </div>
            <div class="bg-white rounded-xl md:rounded-2xl border border-amber-100/90 shadow-sm p-3 sm:p-4 md:p-5 col-span-2 lg:col-span-1">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">Canal principal</p>
                <p class="text-sm font-semibold text-gray-900 mt-2">{{ $nm['by_channel'][0]['label'] ?? '—' }}</p>
                <p class="text-[10px] text-gray-500 mt-0.5">{{ $nm['by_channel'][0]['total'] ?? 0 }} envíos</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="xl:col-span-2 bg-white rounded-2xl border border-amber-100/90 shadow-sm p-5 md:p-6">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Pedidos · últimos 7 días</h2>
                    <span class="text-xs text-gray-500">Volumen diario</span>
                </div>
                <div class="flex items-end justify-between gap-1 min-h-[11rem] px-1 border-b border-gray-200 pb-2">
                    @foreach($orders_by_day as $day)
                        @php
                            $barPx = $max_day_count > 0
                                ? max(10, (int) round(($day['count'] / $max_day_count) * 112))
                                : 10;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end gap-1 min-w-0 min-h-[11rem]">
                            <span class="text-xs font-semibold text-gray-700 tabular-nums leading-none">{{ $day['count'] }}</span>
                            <div class="w-full max-w-[44px] mx-auto rounded-t-md bg-gradient-to-t from-[var(--bf-red)] to-[var(--bf-orange)] shadow-sm" style="height: {{ $barPx }}px" title="{{ $day['short'] }}: {{ $day['count'] }} pedidos"></div>
                            <span class="text-[10px] md:text-xs text-gray-500 truncate w-full text-center mt-auto pt-1">{{ $day['short'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-5 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Contenido publicado</h2>
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between gap-2 border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Productos</span>
                        <a href="{{ route('catalog.products.index') }}" class="font-semibold text-[var(--bf-red)] hover:underline tabular-nums">{{ $catalog_counts['productos'] }}</a>
                    </li>
                    <li class="flex justify-between gap-2 border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Videos</span>
                        <a href="{{ route('videos.index') }}" class="font-semibold text-[var(--bf-red)] hover:underline tabular-nums">{{ $catalog_counts['videos'] }}</a>
                    </li>
                    <li class="flex justify-between gap-2 border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Recetas</span>
                        <a href="{{ route('recetas.index') }}" class="font-semibold text-[var(--bf-red)] hover:underline tabular-nums">{{ $catalog_counts['recetas'] }}</a>
                    </li>
                    <li class="flex justify-between gap-2 border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Combos y packs</span>
                        <a href="{{ route('catalog.offers.bundles') }}" class="font-semibold text-[var(--bf-red)] hover:underline tabular-nums">{{ $catalog_counts['combos'] }}</a>
                    </li>
                    <li class="flex justify-between gap-2">
                        <span class="text-gray-600">Escalas por volumen</span>
                        <a href="{{ route('catalog.offers.volumes') }}" class="font-semibold text-[var(--bf-red)] hover:underline tabular-nums">{{ $catalog_counts['escalas'] }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-amber-100/90 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Últimos pedidos</h2>
                    <a href="{{ route('admin.pedidos.index') }}" class="text-sm text-[var(--bf-red)] font-medium hover:underline">Ver todos</a>
                </div>
                <div class="bf-table-panel bf-table-panel--flush">
                    <table class="bf-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_orders as $pedido)
                                <tr>
                                    <td class="font-mono text-xs">{{ $pedido->id }}</td>
                                    <td class="max-w-[140px] truncate text-sm" title="{{ $pedido->user?->email }}">{{ $pedido->user?->name ?? '—' }}</td>
                                    <td><span class="badge badge-sm badge-ghost">{{ $pedido->status->label() }}</span></td>
                                    <td class="text-right tabular-nums text-sm">{{ $fmtMoney((float) $pedido->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-stone-500 text-sm">Aún no hay pedidos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-amber-100/90 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Seguimiento · stock bajo
                        <span class="ml-1 text-sm font-normal text-gray-500">(<span data-dashboard-low-stock-count>{{ $low_stock->count() }}</span>)</span>
                    </h2>
                    <a href="{{ route('catalog.inventory.index', ['low_only' => 1]) }}" class="text-sm text-[var(--bf-red)] font-medium hover:underline">Gestionar</a>
                </div>
                <div class="bf-table-panel bf-table-panel--flush max-h-72 overflow-y-auto overflow-x-auto">
                    <table class="bf-table bf-table--sticky-head">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-right">Stock</th>
                            </tr>
                        </thead>
                        <tbody data-dashboard-low-stock-body>
                            @forelse($low_stock as $p)
                                <tr data-dashboard-low-stock-product-id="{{ $p->id }}">
                                    <td class="text-sm max-w-[200px] truncate" data-dashboard-low-stock-name>{{ $p->name }}</td>
                                    <td class="text-right font-semibold tabular-nums {{ (float) $p->stock <= (float) $p->min_stock ? 'text-red-600' : 'text-amber-700' }}" data-dashboard-low-stock-value>{{ number_format((float) $p->stock, 1, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr data-dashboard-low-stock-empty>
                                    <td colspan="2" class="text-center py-8 text-gray-500 text-sm">Ningún producto por debajo del umbral configurado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <template id="bf-low-stock-row-tpl">
                <tr>
                    <td class="text-sm max-w-[200px] truncate" data-dashboard-low-stock-name></td>
                    <td class="text-right font-semibold tabular-nums text-amber-700" data-dashboard-low-stock-value></td>
                </tr>
            </template>
        </div>
    </div>
@endsection
