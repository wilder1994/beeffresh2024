@extends('layouts.store')

@section('titulo', 'Estado del pago | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--checkout max-w-lg mx-auto">
    <h1 class="font-brand text-2xl text-[var(--bf-ink)] text-center mb-4">Estado del pago</h1>
    <div class="bf-store-panel p-6 space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-[var(--bf-muted)]">Estado</span>
            <x-payment.status-badge :status="$payment->status" />
        </div>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between gap-4"><dt class="text-[var(--bf-muted)]">Referencia</dt><dd class="font-mono text-xs">{{ $payment->reference }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-[var(--bf-muted)]">Total</dt><dd class="font-semibold tabular-nums">${{ number_format((float) $payment->amount, 0, ',', '.') }}</dd></div>
        </dl>
        @if($payment->order)
            <a href="{{ route('orders.tracking.show', $payment->order) }}" class="bf-btn-primary w-full justify-center">Ver pedido #{{ $payment->order->id }}</a>
        @elseif($payment->status->allowsRetry())
            <a href="{{ route('checkout.show') }}" class="bf-btn-primary w-full justify-center">Reintentar</a>
        @endif
    </div>
</div>
@endsection
