@extends('layouts.app')

@section('titulo', 'Pago '.$payment->reference)
@section('cabecera', 'Detalle de pago')

@section('contenido')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold font-mono">{{ $payment->reference }}</h1>
            <p class="text-sm text-stone-600">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <x-payment.status-badge :status="$payment->status" />
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <section class="bf-ops-panel space-y-2 text-sm">
            <h2 class="bf-ops-panel__title">Transacción</h2>
            <p><span class="text-stone-500">Gateway:</span> {{ $payment->gateway->label() }}</p>
            <p><span class="text-stone-500">ID transacción:</span> {{ $payment->transaction_id ?? '—' }}</p>
            <p><span class="text-stone-500">Método:</span> {{ $payment->payment_method ?? '—' }}</p>
            <p><span class="text-stone-500">Total:</span> <strong>${{ number_format((float) $payment->amount, 0, ',', '.') }} {{ $payment->currency }}</strong></p>
            @if($payment->order_id)
                <p><a href="{{ route('admin.pedidos.show', $payment->order_id) }}" class="text-[var(--bf-brand)] hover:underline">Ver pedido #{{ $payment->order_id }}</a></p>
            @endif
        </section>
        <section class="bf-ops-panel space-y-2 text-sm">
            <h2 class="bf-ops-panel__title">Cliente</h2>
            <p>{{ $payment->user?->name }}</p>
            <p>{{ $payment->user?->email }}</p>
        </section>
    </div>

    <section class="bf-ops-panel">
        <h2 class="bf-ops-panel__title">Intentos ({{ $payment->attempts->count() }})</h2>
        <ul class="divide-y text-sm">
            @foreach($payment->attempts as $attempt)
                <li class="py-2 flex justify-between gap-4">
                    <span>{{ $attempt->type->value }} · {{ $attempt->status }}</span>
                    <span class="text-xs text-stone-500">{{ $attempt->created_at->format('d/m/Y H:i') }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    @if($webhooks->isNotEmpty())
        <section class="bf-ops-panel">
            <h2 class="bf-ops-panel__title">Webhooks relacionados</h2>
            <ul class="divide-y text-sm">
                @foreach($webhooks as $wh)
                    <li class="py-2">
                        <span class="font-medium">{{ $wh->event_type }}</span>
                        · {{ $wh->status->value }}
                        · checksum {{ $wh->checksum_valid ? 'OK' : 'FAIL' }}
                        <span class="text-xs text-stone-500 block">{{ $wh->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
