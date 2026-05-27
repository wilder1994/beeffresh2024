@extends('layouts.store')

@section('titulo', 'Seguimiento pedido #'.$order->id)

@push('bf-realtime-meta')
    <meta name="bf-tracking-token" content="{{ $trackingToken }}">
    @auth
        <meta name="bf-order-id" content="{{ $order->id }}">
    @endauth
@endpush

@section('content')
@php
    $destLat = $order->shipping_latitude !== null ? (float) $order->shipping_latitude : null;
    $destLng = $order->shipping_longitude !== null ? (float) $order->shipping_longitude : null;
@endphp
<div
    class="bf-store-page bf-store-page--wide mx-auto"
    data-order-tracking
    data-feed-url="{{ auth()->check() && auth()->user()->isCustomer() ? route('orders.tracking.feed', $order) : route('orders.tracking.guest-feed', $trackingToken) }}"
    data-order-status="{{ $order->status->value }}"
    data-map-phase="{{ $mapPhase }}"
    data-dest-lat="{{ $destLat }}"
    data-dest-lng="{{ $destLng }}"
    data-maps-api-key="{{ $mapsApiKey }}"
    @if($courierLocation) data-courier-lat="{{ $courierLocation['lat'] }}" data-courier-lng="{{ $courierLocation['lng'] }}" @endif
>
    @auth
        @if(auth()->user()->isCustomer())
            <p class="mb-3">
                <a href="{{ route('customer.orders.index') }}" class="text-sm text-[var(--bf-muted)] hover:text-[var(--bf-brand)] hover:underline">← Mis pedidos</a>
            </p>
        @endif
    @endauth
    <h1 class="font-brand text-2xl text-[var(--bf-ink)] mb-1">Seguimiento del pedido</h1>
    <p class="text-sm text-[var(--bf-muted)] mb-6">Pedido #{{ $order->id }} · {{ $order->created_at->timezone('America/Bogota')->format('d/m/Y H:i') }}</p>

    <div class="bf-tracking-layout">
        <div class="bf-store-panel p-6 space-y-6 bf-tracking-layout__info">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-[var(--bf-muted)]">Estado actual</p>
                    <p class="text-lg font-semibold text-[var(--bf-ink)] mt-1" id="tracking-status-label">{{ $order->status->label() }}</p>
                </div>
                <x-order.status-badge :status="$order->status" />
            </div>

            @if($order->courier)
                <p class="text-sm text-[var(--bf-muted)]" id="tracking-courier-wrap">
                    Domiciliario: <span id="tracking-courier-name" class="font-medium text-[var(--bf-ink)]">{{ $order->courier->name }}</span>
                </p>
            @else
                <p class="text-sm text-[var(--bf-muted)] hidden" id="tracking-courier-wrap">
                    Domiciliario: <span id="tracking-courier-name" class="font-medium text-[var(--bf-ink)]"></span>
                </p>
            @endif

            <x-store.tracking-timeline :entries="$timeline" id="tracking-timeline" />

            <div class="text-sm text-[var(--bf-muted)]">
                Total: <span class="font-semibold text-[var(--bf-brand)] tabular-nums">${{ number_format((float) $order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <aside class="bf-tracking-map-panel" id="tracking-map-panel" aria-label="Ubicación del domiciliario">
            <div id="tracking-map-waiting" class="bf-tracking-map-message @if($mapPhase !== 'waiting') hidden @endif">
                <p>Aún no ha sido recogido por el domiciliario.</p>
                <p class="text-xs mt-2 text-[var(--bf-muted)]">Cuando recoja tu pedido verás su recorrido en el mapa.</p>
            </div>

            <div id="tracking-map-closed" class="bf-tracking-map-message @if($mapPhase !== 'closed') hidden @endif">
                <p class="font-medium text-[var(--bf-ink)]">Tu pedido fue entregado.</p>
                <p class="text-xs mt-2">Gracias por tu compra en BEEF FRESH.</p>
            </div>

            <div id="tracking-map-no-key" class="bf-tracking-map-message hidden">
                <p>El mapa en vivo no está disponible en este momento.</p>
                <p class="text-xs mt-2">Sigue el estado de tu pedido en la columna izquierda.</p>
            </div>

            <div id="tracking-map-live" class="bf-tracking-map-live @if($mapPhase !== 'live') hidden @endif">
                <p class="bf-tracking-map-live__hint text-xs text-[var(--bf-muted)] px-4 py-2 border-b border-[var(--bf-border-brand-subtle)]">
                    Domiciliario en camino — ubicación en tiempo real
                </p>
                <div id="tracking-map-canvas" class="bf-tracking-map-canvas" role="img" aria-label="Mapa del recorrido"></div>
                @if($mapPhase === 'live' && $courierLocation === null)
                    <p class="text-xs text-center text-[var(--bf-muted)] px-4 py-2" id="tracking-map-loc-pending">
                        Esperando señal GPS del domiciliario…
                    </p>
                @else
                    <p class="text-xs text-center text-[var(--bf-muted)] px-4 py-2 hidden" id="tracking-map-loc-pending">
                        Esperando señal GPS del domiciliario…
                    </p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/orderTracking.js')
@endpush
