@php
    use App\Support\Payments\PaymentDevelopmentUrls;
@endphp
@extends('layouts.store')

@section('titulo', 'Pago aprobado | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--checkout max-w-lg mx-auto text-center">
    <div class="bf-payment-result bf-payment-result--success bf-store-panel p-8">
        <div class="bf-payment-result__icon text-emerald-600 mb-4">✓</div>
        <h1 class="font-brand text-2xl text-[var(--bf-ink)] mb-2">¡Pago aprobado!</h1>
        <p class="text-sm text-[var(--bf-muted)] mb-4">Tu pedido fue confirmado y ya está en operaciones.</p>
        @if($payment->order)
            <p class="text-lg font-semibold text-[var(--bf-brand)] mb-6">Pedido #{{ $payment->order->id }}</p>
            <a href="{{ PaymentDevelopmentUrls::localHandoffUrl('orders.tracking.show', $payment->order) }}" class="bf-btn-primary w-full justify-center mb-3">Ver seguimiento</a>
            <a href="{{ PaymentDevelopmentUrls::localHandoffUrl('customer.orders.index') }}" class="text-sm text-[var(--bf-muted)] hover:underline block mb-3">Ver todos mis pedidos</a>
        @endif
        <a href="{{ PaymentDevelopmentUrls::localHandoffUrl('home') }}" class="text-sm text-[var(--bf-muted)] hover:underline">Volver a la tienda</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.bfUpdateCartCount) {
        window.bfUpdateCartCount(0);
    }
});
</script>
@endpush
