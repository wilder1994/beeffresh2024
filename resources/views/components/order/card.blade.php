@props(['order'])

<a href="{{ route('admin.pedidos.show', $order) }}" class="bf-ops-order-card group">
    <div class="bf-ops-order-card__head">
        <div>
            <p class="bf-ops-order-card__id">#{{ $order->id }}</p>
            <p class="bf-ops-order-card__customer">{{ $order->shipping_recipient_name ?? $order->user?->name }}</p>
        </div>
        <x-order.status-badge :status="$order->status" />
    </div>
    <dl class="bf-ops-order-card__meta">
        <div>
            <dt>Zona</dt>
            <dd>{{ $order->shipping_address_line2 ?? $order->shipping_city ?? '—' }}</dd>
        </div>
        <div>
            <dt>Total</dt>
            <dd class="tabular-nums">${{ number_format((float) $order->total, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt>Hace</dt>
            <dd>{{ $order->created_at->diffForHumans(short: true) }}</dd>
        </div>
        @if($order->courier)
            <div>
                <dt>Domiciliario</dt>
                <dd>{{ $order->courier->name }}</dd>
            </div>
        @endif
    </dl>
</a>
