@extends('layouts.app')

@section('titulo', 'Operaciones · Pedidos')
@section('cabecera', 'Centro de operaciones')

@section('contenido')
@php $t = $metrics['totals']; @endphp
<div class="max-w-7xl mx-auto space-y-6" data-ops-polling
     data-feed-url="{{ route('admin.pedidos.feed') }}"
     data-ops-tab="{{ $tab }}"
     data-card-fragment-url="{{ str_replace('/0/', '/__ORDER__/', route('admin.pedidos.card-fragment', ['order' => 0])) }}">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-stone-900">Pedidos en línea</h1>
            <p class="text-sm text-stone-600 mt-0.5">Despacho, asignación y seguimiento en tiempo real.</p>
            <x-realtime.status-indicator class="mt-2" />
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.pedidos.map') }}" class="bf-btn-ghost">Mapa operativo</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        @foreach([
            ['label' => 'Nuevos', 'value' => $t['pending'], 'tab' => 'pending', 'tone' => 'warn'],
            ['label' => 'Preparando', 'value' => $t['preparing'], 'tab' => 'preparing', 'tone' => 'info'],
            ['label' => 'Listos', 'value' => $t['ready'], 'tab' => 'ready', 'tone' => 'indigo'],
            ['label' => 'En camino', 'value' => $t['in_delivery'], 'tab' => 'in_delivery', 'tone' => 'cyan'],
            ['label' => 'Entregados hoy', 'value' => $t['delivered_today'], 'tab' => 'delivered', 'tone' => 'success'],
            ['label' => 'Fallidos', 'value' => $t['failed'], 'tab' => 'failed', 'tone' => 'danger'],
        ] as $metric)
            <a href="{{ route('admin.pedidos.index', ['tab' => $metric['tab']]) }}"
               @class(['bf-ops-metric', 'bf-ops-metric--active' => $tab === $metric['tab'], 'bf-ops-metric--'.$metric['tone']])>
                <span class="bf-ops-metric__value tabular-nums">{{ $metric['value'] }}</span>
                <span class="bf-ops-metric__label">{{ $metric['label'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <nav class="flex flex-wrap gap-1.5" aria-label="Filtrar por estado">
            @foreach([
                'all' => 'Todos',
                'pending' => 'Nuevos',
                'preparing' => 'Preparando',
                'ready' => 'Listos',
                'in_delivery' => 'En camino',
                'delivered' => 'Entregados',
                'returned' => 'Devueltos',
                'cancelled' => 'Cancelados',
            ] as $key => $label)
                <a href="{{ route('admin.pedidos.index', array_filter(['tab' => $key, 'search' => $search])) }}"
                   @class(['bf-ops-tab', 'bf-ops-tab--active' => $tab === $key])>{{ $label }}</a>
            @endforeach
        </nav>
        <form method="GET" class="flex gap-2 w-full sm:w-auto">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="Buscar pedido, cliente, teléfono…" class="bf-input w-full sm:w-64">
            <button type="submit" class="bf-btn-primary">Buscar</button>
        </form>
    </div>

    <p class="text-xs text-stone-500">
        Domiciliarios: <strong class="text-emerald-700">{{ $metrics['available_couriers'] }} libres</strong>
        · <strong class="text-amber-700">{{ $metrics['busy_couriers'] }} ocupados</strong>
        · Ingresos hoy: <strong>${{ number_format($metrics['revenue_today'], 0, ',', '.') }}</strong>
    </p>

    @if($pedidos->isEmpty())
        <div class="bf-ops-empty">No hay pedidos en este filtro.</div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="ops-order-grid">
            @foreach($pedidos as $pedido)
                <x-order.card :order="$pedido" />
            @endforeach
        </div>
        <div class="mt-6">{{ $pedidos->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
    @vite('resources/js/operationsPolling.js')
@endpush
