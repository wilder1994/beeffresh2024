@extends('layouts.store')

@section('titulo', 'Pago no completado | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--checkout max-w-lg mx-auto text-center">
    <div class="bf-payment-result bf-payment-result--failed bf-store-panel p-8">
        <div class="bf-payment-result__icon text-red-600 mb-4">!</div>
        <h1 class="font-brand text-2xl text-[var(--bf-ink)] mb-2">No pudimos completar el pago</h1>
        <p class="text-sm text-[var(--bf-muted)] mb-4">Tu carrito sigue intacto. Puedes intentar de nuevo con otro método.</p>
        <x-payment.status-badge :status="$payment->status" class="mb-6" />
        <a href="{{ route('checkout.show') }}" class="bf-btn-primary w-full justify-center mb-3">Reintentar pago</a>
        <a href="{{ route('carrito.ver') }}" class="text-sm text-[var(--bf-muted)] hover:underline">Volver al carrito</a>
    </div>
</div>
@endsection
