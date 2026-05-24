<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #{{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: ui-monospace, monospace; font-size: 12px; line-height: 1.35; color: #111; max-width: 320px; margin: 0 auto; padding: 12px; }
        .center { text-align: center; }
        .brand { font-size: 16px; font-weight: 700; letter-spacing: 0.05em; }
        hr { border: none; border-top: 1px dashed #999; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .muted { color: #555; font-size: 11px; }
        @media print { body { max-width: none; } .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="center brand">BEEF FRESH</div>
    <p class="center muted">Ticket operacional</p>
    <hr>
    <p><strong>Pedido #{{ $order->id }}</strong></p>
    <p class="muted">{{ $order->created_at->format('d/m/Y H:i') }} · intento {{ $order->delivery_attempt }}</p>
    <hr>
    <p><strong>{{ $order->shipping_recipient_name }}</strong></p>
    <p>{{ $order->shipping_phone }}</p>
    <p>{{ $order->shipping_address_line1 }}</p>
    @if($order->shipping_address_line2)<p>Barrio: {{ $order->shipping_address_line2 }}</p>@endif
    <p>{{ $order->shipping_city }}, {{ $order->shipping_state }}</p>
    @if($order->shipping_notes)<p class="muted">Notas: {{ $order->shipping_notes }}</p>@endif
    <hr>
    @foreach($order->items as $item)
        <div class="row">
            <span>{{ $item->line_label ?? 'Ítem' }}</span>
            <span>{{ $item->quantity }} {{ $item->sale_unit?->value }}</span>
        </div>
    @endforeach
    <hr>
    <div class="row"><strong>Total</strong><strong>${{ number_format((float) $order->total, 0, ',', '.') }}</strong></div>
    @if($order->courier)
        <hr>
        <p>Domiciliario: <strong>{{ $order->courier->name }}</strong></p>
    @endif
    <hr>
    <p class="center muted">#{{ $order->tracking_token }}</p>
    <p class="center no-print" style="margin-top:16px"><button onclick="window.print()">Imprimir</button></p>
</body>
</html>
