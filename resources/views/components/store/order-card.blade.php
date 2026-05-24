@props(['order'])

@php
    $isActive = ! in_array($order->status, \App\Enums\OrderStatus::terminalStatuses(), true);
@endphp

<a
    href="{{ route('orders.tracking.show', $order) }}"
    class="bf-store-order-card group block"
>
    <div class="bf-store-order-card__head">
        <div>
            <p class="bf-store-order-card__id">Pedido #{{ $order->id }}</p>
            <p class="bf-store-order-card__date">{{ $order->created_at->format('d/m/Y · H:i') }}</p>
        </div>
        <x-order.status-badge :status="$order->status" />
    </div>

    <dl class="bf-store-order-card__meta">
        <div>
            <dt>Total</dt>
            <dd class="tabular-nums font-semibold text-[var(--bf-brand)]">${{ number_format((float) $order->total, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt>Productos</dt>
            <dd>{{ $order->items_count ?? $order->items->count() }}</dd>
        </div>
        <div>
            <dt>Entrega</dt>
            <dd>{{ $order->shipping_city ?? '—' }}{{ $order->shipping_address_line2 ? ' · '.$order->shipping_address_line2 : '' }}</dd>
        </div>
        @if($order->courier)
            <div>
                <dt>Domiciliario</dt>
                <dd>{{ $order->courier->name }}</dd>
            </div>
        @endif
    </dl>

    <p class="bf-store-order-card__cta">
        {{ $isActive ? 'Ver seguimiento en vivo →' : 'Ver detalle del pedido →' }}
    </p>
</a>
