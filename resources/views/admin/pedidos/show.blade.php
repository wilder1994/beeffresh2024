@extends('layouts.app')

@push('bf-realtime-meta')
    <meta name="bf-order-id" content="{{ $order->id }}">
@endpush

@section('titulo', 'Pedido #'.$order->id)
@section('cabecera', 'Pedido #'.$order->id)

@section('contenido')
<div class="max-w-6xl mx-auto space-y-6">
    @if(session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-bold text-stone-900">Pedido #{{ $order->id }}</h1>
                <x-order.status-badge :status="$order->status" />
            </div>
            <p class="text-sm text-stone-600 mt-1">{{ $order->created_at->format('d/m/Y H:i') }} · intento {{ $order->delivery_attempt }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.pedidos.index') }}" class="bf-btn-ghost">← Volver</a>
            <a href="{{ route('admin.pedidos.ticket.show', $order) }}" target="_blank" class="bf-btn-primary">Imprimir ticket</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="bf-ops-panel">
                <h2 class="bf-ops-panel__title">Productos</h2>
                <ul class="divide-y divide-stone-200/80">
                    @foreach($order->items as $item)
                        <li class="py-3 flex justify-between gap-4 text-sm">
                            <div>
                                <p class="font-medium text-stone-900">{{ $item->line_label ?? $item->product?->name ?? $item->offer?->name ?? 'Ítem' }}</p>
                                <p class="text-stone-500">{{ $item->quantity }} {{ $item->sale_unit?->value ?? 'u' }} × ${{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <span class="font-semibold tabular-nums">${{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 pt-4 border-t flex justify-between font-bold">
                    <span>Total</span>
                    <span class="text-[var(--bf-brand)] tabular-nums">${{ number_format((float) $order->total, 0, ',', '.') }}</span>
                </div>
                @if((float) $order->redelivery_fee > 0)
                    <p class="text-xs text-amber-700 mt-2">Reenvío: +${{ number_format((float) $order->redelivery_fee, 0, ',', '.') }}</p>
                @endif
            </section>

            <section class="bf-ops-panel">
                <h2 class="bf-ops-panel__title">Historial</h2>
                <ol class="bf-ops-timeline" id="admin-order-timeline">
                    @foreach($order->statusLogs as $log)
                        <li class="bf-ops-timeline__item">
                            <span class="bf-ops-timeline__dot"></span>
                            <div>
                                <p class="font-medium text-sm">{{ $log->to_status->label() }}</p>
                                <p class="text-xs text-stone-500">{{ $log->created_at->format('d/m/Y H:i') }}@if($log->user) · {{ $log->user->name }}@endif</p>
                                @if($log->note)<p class="text-xs text-stone-600 mt-0.5">{{ $log->note }}</p>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        <div class="space-y-6">
            <section class="bf-ops-panel">
                <h2 class="bf-ops-panel__title">Cliente y entrega</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-stone-500 text-xs uppercase">Cliente</dt><dd class="font-medium">{{ $order->shipping_recipient_name }}</dd></div>
                    <div><dt class="text-stone-500 text-xs uppercase">Teléfono</dt><dd>{{ $order->shipping_phone }}</dd></div>
                    <div><dt class="text-stone-500 text-xs uppercase">Dirección</dt><dd>{{ $order->shipping_address_line1 }}@if($order->shipping_address_line2), {{ $order->shipping_address_line2 }}@endif</dd></div>
                    <div><dt class="text-stone-500 text-xs uppercase">Ciudad</dt><dd>{{ $order->shipping_city }}, {{ $order->shipping_state }}</dd></div>
                    @if($order->shipping_notes)
                        <div><dt class="text-stone-500 text-xs uppercase">Notas</dt><dd>{{ $order->shipping_notes }}</dd></div>
                    @endif
                    <div><dt class="text-stone-500 text-xs uppercase">Pago</dt><dd>{{ $order->payment_method?->label() ?? '—' }}</dd></div>
                </dl>
            </section>

            <section class="bf-ops-panel">
                <h2 class="bf-ops-panel__title">Domiciliario</h2>
                @if($order->courier)
                    <p class="font-medium" id="admin-order-courier">{{ $order->courier->name }}</p>
                    <p class="text-xs text-stone-500 mt-1">Asignado {{ $order->assigned_at?->diffForHumans() }}</p>
                @else
                    <p class="text-sm text-stone-500">Sin asignar</p>
                @endif
            </section>

            <section class="bf-ops-panel space-y-3">
                <h2 class="bf-ops-panel__title">Acciones</h2>
                @if($order->status === \App\Enums\OrderStatus::Pending)
                    <form method="POST" action="{{ route('admin.pedidos.start-preparing', $order) }}">@csrf
                        <button type="submit" class="bf-btn-primary w-full justify-center">Iniciar alistamiento</button>
                    </form>
                @endif
                @if($order->status === \App\Enums\OrderStatus::Preparing)
                    <form method="POST" action="{{ route('admin.pedidos.mark-ready', $order) }}">@csrf
                        <button type="submit" class="bf-btn-primary w-full justify-center">Marcar listo y asignar</button>
                    </form>
                @endif
                @if($order->status === \App\Enums\OrderStatus::ReturnedToStore)
                    <form method="POST" action="{{ route('admin.pedidos.redispatch', $order) }}" class="space-y-2">@csrf
                        <label class="bf-label-muted">Cargo reenvío (opcional)</label>
                        <input type="number" name="redelivery_fee" min="0" step="1000" class="bf-input" placeholder="0">
                        <textarea name="note" class="bf-textarea min-h-[3rem]" placeholder="Nota de reprogramación"></textarea>
                        <button type="submit" class="bf-btn-primary w-full justify-center">Reprogramar entrega</button>
                    </form>
                @endif
                @if(!in_array($order->status, [\App\Enums\OrderStatus::Delivered, \App\Enums\OrderStatus::Cancelled], true))
                    <form method="POST" action="{{ route('admin.pedidos.cancel', $order) }}" class="space-y-2">@csrf
                        <textarea name="note" class="bf-textarea min-h-[3rem]" placeholder="Motivo cancelación"></textarea>
                        <button type="submit" class="bf-btn-ghost w-full justify-center text-red-700 border-red-200">Cancelar pedido</button>
                    </form>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
