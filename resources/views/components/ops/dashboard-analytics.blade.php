@props([
    'kpi' => [],
    'analytics' => [],
    'showRevenue' => false,
    'showRanking' => false,
    'recentOrders' => collect(),
    'lowStock' => null,
])

@php
    $fmtMoney = static fn (float $n): string => '$'.number_format($n, 0, ',', '.');
    $funnel = $analytics['stage_funnel'] ?? [];
    $zones = $analytics['orders_by_zone'] ?? [];
    $hours = $analytics['sales_by_hour'] ?? [];
    $zoneCounts = array_column($zones, 'count');
    $maxZone = $zoneCounts !== [] ? max(1, ...$zoneCounts) : 1;
    $hourOrders = array_map(static fn ($h) => (int) ($h['orders'] ?? 0), $hours);
    $maxHourOrders = $hourOrders !== [] ? max(1, ...$hourOrders) : 1;
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-2 sm:gap-3 mb-4 md:mb-6">
    @if($showRevenue)
        <div class="bf-ops-metric bf-ops-metric--success">
            <span class="bf-ops-metric__value tabular-nums">{{ $fmtMoney((float) ($kpi['revenue_today'] ?? 0)) }}</span>
            <span class="bf-ops-metric__label">Ingresos hoy</span>
        </div>
    @endif
    <div class="bf-ops-metric bf-ops-metric--success">
        <span class="bf-ops-metric__value tabular-nums">{{ $kpi['delivered_today'] ?? 0 }}</span>
        <span class="bf-ops-metric__label">Entregados hoy</span>
    </div>
    <div class="bf-ops-metric bf-ops-metric--cyan">
        <span class="bf-ops-metric__value tabular-nums">{{ $kpi['active'] ?? $kpi['handled_active'] ?? 0 }}</span>
        <span class="bf-ops-metric__label">Activos</span>
    </div>
    <div class="bf-ops-metric bf-ops-metric--danger">
        <span class="bf-ops-metric__value tabular-nums">{{ $kpi['failed'] ?? 0 }}</span>
        <span class="bf-ops-metric__label">Fallidos</span>
    </div>
    <div class="bf-ops-metric bf-ops-metric--info">
        <span class="bf-ops-metric__value tabular-nums">{{ $kpi['avg_delivery_minutes'] ?? $kpi['avg_ready_to_delivered_minutes'] ?? '—' }}@if(($kpi['avg_delivery_minutes'] ?? $kpi['avg_ready_to_delivered_minutes'] ?? null) !== null) min @endif</span>
        <span class="bf-ops-metric__label">Tiempo prom.</span>
    </div>
    <div class="bf-ops-metric bf-ops-metric--indigo">
        <span class="bf-ops-metric__value tabular-nums">{{ $kpi['sla_percent'] ?? '—' }}@if(isset($kpi['sla_percent']))% @endif</span>
        <span class="bf-ops-metric__label">SLA despacho</span>
    </div>
    <div class="bf-ops-metric bf-ops-metric--warn">
        <span class="bf-ops-metric__value tabular-nums">{{ $kpi['available_couriers'] ?? 0 }}/{{ ($kpi['available_couriers'] ?? 0) + ($kpi['busy_couriers'] ?? 0) }}</span>
        <span class="bf-ops-metric__label">Couriers libres</span>
    </div>
    @if(isset($kpi['pending_pool']))
        <div class="bf-ops-metric bf-ops-metric--warn">
            <span class="bf-ops-metric__value tabular-nums">{{ $kpi['pending_pool'] }}</span>
            <span class="bf-ops-metric__label">Nuevos sin tomar</span>
        </div>
    @elseif($showRevenue)
        <div class="bf-ops-metric">
            <span class="bf-ops-metric__value tabular-nums">{{ $kpi['low_stock_count'] ?? 0 }}</span>
            <span class="bf-ops-metric__label">Stock bajo</span>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6">
    <section class="xl:col-span-2 bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 md:p-5">
        <div class="flex items-center justify-between gap-2 mb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600">{{ $showRevenue ? 'Ventas por hora' : 'Pedidos por hora' }}</h2>
            <span class="text-xs text-stone-500">Hoy</span>
        </div>
        <div class="flex items-end gap-0.5 min-h-[9rem] overflow-x-auto pb-1">
            @foreach($hours as $hour)
                @php
                    $count = (int) ($hour['orders'] ?? 0);
                    $barPx = $maxHourOrders > 0 ? max(4, (int) round(($count / $maxHourOrders) * 96)) : 4;
                @endphp
                <div class="flex flex-col items-center justify-end min-w-[1.35rem] flex-1 gap-1" title="{{ $hour['label'] }}: {{ $count }} pedidos">
                    <span class="text-[9px] font-semibold text-stone-600 tabular-nums">{{ $count > 0 ? $count : '' }}</span>
                    <div class="w-full max-w-[1.25rem] rounded-t bg-gradient-to-t from-[var(--bf-red)] to-[var(--bf-orange)]" style="height: {{ $barPx }}px"></div>
                    <span class="text-[8px] text-stone-400">{{ $hour['hour'] % 3 === 0 ? $hour['label'] : '' }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 md:p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600 mb-3">Embudo operativo</h2>
        <ul class="space-y-2 text-sm">
            @foreach([
                ['label' => 'Nuevos', 'value' => $funnel['pending'] ?? 0, 'tone' => 'text-amber-700'],
                ['label' => 'Preparando', 'value' => $funnel['preparing'] ?? 0, 'tone' => 'text-blue-700'],
                ['label' => 'Listos', 'value' => $funnel['ready'] ?? 0, 'tone' => 'text-indigo-700'],
                ['label' => 'En camino', 'value' => $funnel['in_delivery'] ?? 0, 'tone' => 'text-cyan-700'],
                ['label' => 'Entregados hoy', 'value' => $funnel['delivered_today'] ?? 0, 'tone' => 'text-emerald-700'],
            ] as $step)
                <li class="flex items-center justify-between gap-3 border-b border-stone-100 pb-2">
                    <span class="text-stone-600">{{ $step['label'] }}</span>
                    <span class="font-bold tabular-nums {{ $step['tone'] }}">{{ $step['value'] }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
    <section class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 md:p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600 mb-3">Pedidos por zona (7d)</h2>
        <ul class="space-y-2">
            @forelse($zones as $zone)
                @php $width = max(8, (int) round(((int) $zone['count'] / $maxZone) * 100)); @endphp
                <li>
                    <div class="flex justify-between text-xs mb-1 gap-2">
                        <span class="truncate text-stone-700">{{ $zone['zone'] }}</span>
                        <span class="font-semibold tabular-nums text-stone-900">{{ $zone['count'] }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-stone-100 overflow-hidden">
                        <div class="h-full rounded-full bg-[var(--bf-rust)]" style="width: {{ $width }}%"></div>
                    </div>
                </li>
            @empty
                <li class="text-sm text-stone-500">Sin datos de zona.</li>
            @endforelse
        </ul>
    </section>

    @if($showRanking)
        <section class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 md:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600 mb-3">Despachadores hoy</h2>
            <div class="bf-table-panel bf-table-panel--flush">
                <table class="bf-table">
                    <thead>
                        <tr>
                            <th>Despachador</th>
                            <th class="text-right">Tomados</th>
                            <th class="text-right">Entregados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['dispatcher_ranking'] ?? [] as $row)
                            <tr>
                                <td class="text-sm">{{ $row['name'] }}</td>
                                <td class="text-right tabular-nums">{{ $row['handled_today'] }}</td>
                                <td class="text-right tabular-nums font-semibold">{{ $row['delivered_today'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-stone-500 text-sm">Sin actividad hoy.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section
            class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 md:p-5"
            data-ops-dashboard-map
            data-map-points='@json($analytics['heatmap_points'] ?? [])'
            data-api-key="{{ config('services.google.maps_api_key') }}"
        >
            <div class="flex items-center justify-between gap-2 mb-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Mapa calor pedidos</h2>
                <a href="{{ route('admin.pedidos.map') }}" class="text-xs text-[var(--bf-red)] font-medium hover:underline">Mapa completo</a>
            </div>
            <div id="ops-dashboard-heatmap" class="h-52 rounded-xl bg-stone-100 border border-stone-200 overflow-hidden"></div>
        </section>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
    <section class="bg-white rounded-2xl border border-amber-100/90 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-stone-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Pedidos recientes</h2>
            <a href="{{ route('admin.pedidos.index') }}" class="text-xs text-[var(--bf-red)] font-medium hover:underline">Ver todos</a>
        </div>
        <div class="bf-table-panel bf-table-panel--flush">
            <table class="bf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        @if($showRevenue)<th class="text-right">Total</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $pedido)
                        <tr>
                            <td class="font-mono text-xs"><a href="{{ route('admin.pedidos.show', $pedido) }}" class="text-[var(--bf-red)] hover:underline">#{{ $pedido->id }}</a></td>
                            <td class="text-sm truncate max-w-[8rem]">{{ $pedido->shipping_recipient_name }}</td>
                            <td><x-order.status-badge :status="$pedido->status" /></td>
                            @if($showRevenue)<td class="text-right tabular-nums text-sm">{{ $fmtMoney((float) $pedido->total) }}</td>@endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $showRevenue ? 4 : 3 }}" class="text-center py-8 text-stone-500 text-sm">Sin pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($lowStock !== null)
        <section class="bg-white rounded-2xl border border-amber-100/90 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-stone-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Stock bajo</h2>
                <a href="{{ route('catalog.inventory.index', ['low_only' => 1]) }}" class="text-xs text-[var(--bf-red)] font-medium hover:underline">Inventario</a>
            </div>
            <div class="bf-table-panel bf-table-panel--flush max-h-64 overflow-y-auto">
                <table class="bf-table">
                    <thead><tr><th>Producto</th><th class="text-right">Stock</th></tr></thead>
                    <tbody data-dashboard-low-stock-body>
                        @forelse($lowStock as $p)
                            <tr data-dashboard-low-stock-product-id="{{ $p->id }}">
                                <td class="text-sm truncate max-w-[12rem]">{{ $p->name }}</td>
                                <td class="text-right tabular-nums font-semibold text-amber-700">{{ number_format((float) $p->stock, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center py-8 text-stone-500 text-sm">Sin alertas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 md:p-5 flex flex-col justify-center">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600 mb-2">Accesos rápidos</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.pedidos.index') }}" class="bf-btn-primary text-sm">Mis pedidos</a>
                <a href="{{ route('admin.pedidos.map') }}" class="bf-btn-ghost text-sm">Mapa operativo</a>
            </div>
            <p class="text-xs text-stone-500 mt-3">Panel personal sin datos financieros. Toma pedidos pendientes desde la cola.</p>
        </section>
    @endif
</div>
