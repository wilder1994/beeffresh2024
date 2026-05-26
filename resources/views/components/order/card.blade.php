@props(['order'])

<a
    href="{{ route('admin.pedidos.show', $order) }}"
    class="bf-ops-order-card group"
    data-ops-order-id="{{ $order->id }}"
    data-ops-order-status="{{ $order->status->value }}"
    data-ops-order-updated-at="{{ $order->updated_at?->toIso8601String() }}"
>
    <div class="bf-ops-order-card__head">
        <div>
            <p class="bf-ops-order-card__id">#{{ $order->id }}</p>
            <p class="bf-ops-order-card__customer">{{ $order->shipping_recipient_name ?? $order->user?->name }}</p>
        </div>
        <x-order.status-badge :status="$order->status" data-ops-order-badge />
    </div>
    <dl class="bf-ops-order-card__meta">
        <div>
            <dt>Zona</dt>
            <dd data-ops-order-zone>{{ $order->shipping_address_line2 ?? $order->shipping_city ?? '—' }}</dd>
        </div>
        <div>
            <dt>Total</dt>
            <dd class="tabular-nums">${{ number_format((float) $order->total, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt>Hace</dt>
            <dd>{{ $order->created_at->diffForHumans(short: true) }}</dd>
        </div>
        <div @class(['hidden' => ! $order->courier]) data-ops-order-courier-row>
            <dt>Domiciliario</dt>
            <dd data-ops-order-courier>{{ $order->courier?->name }}</dd>
        </div>
    </dl>
    <p class="text-[10px] text-stone-400 mt-2" data-ops-order-timeline>
        Actualizado {{ $order->updated_at?->diffForHumans(short: true) }}
    </p>
</a>
