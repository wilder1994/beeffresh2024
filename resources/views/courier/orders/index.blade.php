@extends('layouts.app')

@push('bf-realtime-meta')
    <meta name="bf-courier-id" content="{{ auth()->id() }}">
@endpush

@section('titulo', 'Mis entregas')
@section('cabecera', 'Panel domiciliario')

@section('contenido')
@php
    $gpsActive = $myOrders->contains(
        fn ($o) => in_array($o->status, \App\Enums\OrderStatus::activeCourierStatuses(), true)
    );
@endphp
<div
    class="max-w-3xl mx-auto space-y-8"
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
    @if($errors->has('accept'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('accept') }}</div>
    @endif

    <div>
        <h1 class="text-xl font-bold text-stone-900">Mis entregas</h1>
        <p class="text-sm text-stone-600">Acepta pedidos listos en tienda o continúa tus entregas en curso.</p>
    </div>

    <section
        class="space-y-3"
        data-courier-pool
        data-feed-url="{{ route('courier.orders.pool-feed') }}"
        data-card-fragment-url="{{ route('courier.orders.pool-card', ['order' => '__ORDER__']) }}"
        data-accept-url-template="{{ route('courier.orders.accept', ['order' => '__ORDER__']) }}"
        data-can-accept="{{ $canAccept ? '1' : '0' }}"
    >
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Disponibles para tomar</h2>
            <span id="courier-pool-count" class="text-xs text-stone-500">{{ $poolOrders->count() }} en cola</span>
        </div>

        <div id="courier-pool-list" class="space-y-3">
            @if($poolOrders->isEmpty())
                <div class="bf-courier-pool-empty text-sm">No hay pedidos listos sin asignar.</div>
            @else
                @foreach($poolOrders as $order)
                    <x-courier.pool-order-card :order="$order" :canAccept="$canAccept" />
                @endforeach
            @endif
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Mis entregas activas</h2>

        @if($myOrders->isEmpty())
            <div class="bf-ops-empty text-sm">No tienes entregas asignadas.</div>
        @else
            <div class="space-y-3">
                @foreach($myOrders as $order)
                    <a href="{{ route('courier.orders.show', $order) }}" class="bf-ops-order-card block">
                        <div class="bf-ops-order-card__head">
                            <div>
                                <p class="bf-ops-order-card__id">#{{ $order->id }}</p>
                                <p class="bf-ops-order-card__customer">{{ $order->shipping_recipient_name }}</p>
                            </div>
                            <x-order.status-badge :status="$order->status" />
                        </div>
                        <p class="text-sm text-stone-600 mt-2">{{ $order->shipping_address_line2 ?? $order->shipping_city }} · {{ $order->shipping_phone }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    @vite('resources/js/courierOps.js')
@endpush
