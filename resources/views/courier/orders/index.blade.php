@extends('layouts.app')

@section('titulo', 'Mis entregas')
@section('cabecera', 'Panel domiciliario')

@section('contenido')
<div class="max-w-3xl mx-auto space-y-6" data-courier-location data-location-url="{{ route('courier.location.update') }}">
    @if(session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div>
        <h1 class="text-xl font-bold text-stone-900">Mis entregas</h1>
        <p class="text-sm text-stone-600">Pedidos asignados y en curso.</p>
    </div>

    @if($orders->isEmpty())
        <div class="bf-ops-empty">No tienes entregas activas.</div>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
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
</div>
@endsection

@push('scripts')
    @vite('resources/js/courierOps.js')
@endpush
