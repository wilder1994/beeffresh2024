@extends('layouts.app')

@push('bf-realtime-meta')
    <meta name="bf-courier-id" content="{{ auth()->id() }}">
@endpush

@section('titulo', 'Entrega #'.$order->id)
@section('cabecera', 'Entrega #'.$order->id)

@section('contenido')
@php
    $mapsUrl = $order->shipping_latitude && $order->shipping_longitude
        ? 'https://www.google.com/maps/dir/?api=1&destination='.$order->shipping_latitude.','.$order->shipping_longitude
        : 'https://www.google.com/maps/search/?api=1&query='.urlencode(trim(($order->shipping_address_line1 ?? '').' '.$order->shipping_city));
@endphp
@php
    $gpsActive = in_array($order->status, \App\Enums\OrderStatus::activeCourierStatuses(), true);
@endphp
<div
    class="max-w-lg mx-auto space-y-6"
    data-courier-location
    data-location-url="{{ route('courier.location.update') }}"
    data-tracking-mode="{{ $gpsActive ? 'active' : 'idle' }}"
    data-interval-active-ms="{{ config('realtime.courier_gps.interval_active_ms') }}"
    data-interval-idle-ms="{{ config('realtime.courier_gps.interval_idle_ms') }}"
    data-min-send-meters="{{ config('realtime.courier_gps.min_send_meters') }}"
>
    @if(session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <x-order.status-badge :status="$order->status" />
        <a href="{{ route('courier.orders.index') }}" class="bf-btn-ghost text-sm">← Volver</a>
    </div>

    <section class="bf-ops-panel space-y-2 text-sm">
        <h2 class="font-semibold text-stone-900">{{ $order->shipping_recipient_name }}</h2>
        <p>{{ $order->shipping_phone }}</p>
        <p>{{ $order->shipping_address_line1 }}@if($order->shipping_address_line2), {{ $order->shipping_address_line2 }}@endif</p>
        <p>{{ $order->shipping_city }}, {{ $order->shipping_state }}</p>
        @if($order->shipping_notes)<p class="text-stone-600">{{ $order->shipping_notes }}</p>@endif
        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="bf-btn-primary w-full justify-center mt-2">Abrir en Google Maps</a>
    </section>

    <section class="bf-ops-panel">
        <h3 class="bf-ops-panel__title">Productos</h3>
        <ul class="text-sm space-y-1">
            @foreach($order->items as $item)
                <li>{{ $item->line_label ?? 'Ítem' }} · {{ $item->quantity }} {{ $item->sale_unit?->value }}</li>
            @endforeach
        </ul>
    </section>

    <section class="bf-ops-panel space-y-3">
        <h3 class="bf-ops-panel__title">Acciones</h3>
        @if($order->status === \App\Enums\OrderStatus::ReadyForDelivery && $order->courier_id === null)
            @can('accept', $order)
                <form method="POST" action="{{ route('courier.orders.accept', $order) }}">@csrf
                    <button type="submit" class="bf-btn-primary w-full justify-center">Aceptar este pedido</button>
                </form>
            @else
                <p class="text-sm text-stone-600">Este pedido está disponible para otro domiciliario o debes finalizar tu entrega actual.</p>
            @endcan
        @endif
        @if($order->status === \App\Enums\OrderStatus::ReadyForDelivery && $order->courier_id === auth()->id())
            <form method="POST" action="{{ route('courier.orders.picked-up', $order) }}">@csrf
                <button type="submit" class="bf-btn-primary w-full justify-center">Confirmar recogida</button>
            </form>
        @endif
        @if($order->status === \App\Enums\OrderStatus::PickedUp)
            <form method="POST" action="{{ route('courier.orders.in-transit', $order) }}">@csrf
                <button type="submit" class="bf-btn-primary w-full justify-center">Marcar en camino</button>
            </form>
        @endif
        @if($order->status === \App\Enums\OrderStatus::InTransit)
            <div data-courier-delivery class="space-y-3">
                <form method="POST" action="{{ route('courier.orders.delivered', $order) }}" data-delivery-form class="space-y-3">@csrf
                    <input type="hidden" name="signature" data-signature-input>
                    <input type="hidden" name="latitude" data-lat-input>
                    <input type="hidden" name="longitude" data-lng-input>
                    <label class="bf-label">Firma del cliente</label>
                    <canvas data-signature-canvas class="w-full h-40 border border-stone-300 rounded-lg bg-white touch-none"></canvas>
                    <button type="button" data-signature-clear class="bf-btn-ghost text-sm">Limpiar firma</button>
                    <button type="submit" class="bf-btn-primary w-full justify-center">Confirmar entrega</button>
                </form>
                <form method="POST" action="{{ route('courier.orders.failed', $order) }}" enctype="multipart/form-data" class="space-y-2 pt-2 border-t">@csrf
                    <label class="bf-label text-red-800">Entrega fallida</label>
                    <input type="file" name="media" accept="image/*,video/*" class="bf-file" required>
                    <textarea name="notes" class="bf-textarea min-h-[3rem]" placeholder="Motivo / observación" required></textarea>
                    <button type="submit" class="bf-btn-ghost w-full justify-center text-red-700">Reportar fallida</button>
                </form>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    @vite('resources/js/courierOps.js')
@endpush
