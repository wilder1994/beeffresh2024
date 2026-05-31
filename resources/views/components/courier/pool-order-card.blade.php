@props(['order', 'canAccept' => false])

<article class="bf-ops-order-card" data-courier-pool-order-id="{{ $order->id }}">
    <div class="bf-ops-order-card__head">
        <div>
            <p class="bf-ops-order-card__id">#{{ $order->id }}</p>
            <p class="bf-ops-order-card__customer">{{ $order->shipping_recipient_name }}</p>
        </div>
        <x-order.status-badge :status="$order->status" />
    </div>
    <p class="text-sm text-stone-600 mt-2">
        {{ $order->shipping_address_line2 ?? $order->shipping_city }}
        · {{ $order->shipping_phone }}
    </p>
    <p class="text-xs text-stone-500 mt-1">
        Listo {{ $order->ready_at?->diffForHumans() ?? 'recientemente' }}
    </p>
    <div class="mt-3 flex flex-wrap gap-2" data-courier-pool-actions>
        <a href="{{ route('courier.orders.show', $order) }}" class="bf-btn-ghost text-sm">Ver detalle</a>
        @if($canAccept)
            <form method="POST" action="{{ route('courier.orders.accept', $order) }}">
                @csrf
                <button type="submit" class="bf-btn-primary text-sm">Aceptar pedido</button>
            </form>
        @else
            <p class="text-xs text-amber-700 self-center" data-courier-pool-busy-msg>Finaliza tu entrega actual para aceptar otro.</p>
        @endif
    </div>
</article>
