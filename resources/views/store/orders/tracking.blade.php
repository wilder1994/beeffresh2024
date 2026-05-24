@extends('layouts.store')

@section('titulo', 'Seguimiento pedido #'.$order->id)

@section('content')
<div class="bf-store-page bf-store-page--wide max-w-2xl mx-auto" data-order-tracking data-feed-url="{{ auth()->check() && auth()->user()->isCustomer() ? route('orders.tracking.feed', $order) : route('orders.tracking.guest-feed', $trackingToken) }}">
    <h1 class="font-brand text-2xl text-[var(--bf-ink)] mb-1">Seguimiento del pedido</h1>
    <p class="text-sm text-[var(--bf-muted)] mb-6">Pedido #{{ $order->id }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>

    <div class="bf-store-panel p-6 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-[var(--bf-muted)]">Estado actual</p>
                <p class="text-lg font-semibold text-[var(--bf-ink)] mt-1" id="tracking-status-label">{{ $order->status->label() }}</p>
            </div>
            <x-order.status-badge :status="$order->status" />
        </div>

        @if($order->courier)
            <p class="text-sm text-[var(--bf-muted)]">Domiciliario: <span class="font-medium text-[var(--bf-ink)]">{{ $order->courier->name }}</span></p>
        @endif

        <ol class="bf-ops-timeline" id="tracking-timeline">
            @foreach($order->statusLogs as $log)
                <li class="bf-ops-timeline__item">
                    <span class="bf-ops-timeline__dot"></span>
                    <div>
                        <p class="font-medium text-sm">{{ $log->to_status->label() }}</p>
                        <p class="text-xs text-[var(--bf-muted)]">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="text-sm text-[var(--bf-muted)]">
            Total: <span class="font-semibold text-[var(--bf-brand)] tabular-nums">${{ number_format((float) $order->total, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/orderTracking.js')
@endpush
