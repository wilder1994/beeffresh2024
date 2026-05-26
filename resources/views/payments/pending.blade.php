@extends('layouts.store')

@section('titulo', 'Pago pendiente | BEEF FRESH')

@push('bf-realtime-meta')
    @auth
        <meta name="bf-payment-uuid" content="{{ $payment->uuid }}">
    @endauth
@endpush

@section('content')
<div
    class="bf-store-page bf-store-page--checkout max-w-lg mx-auto text-center"
    data-bf-payment-process
    data-phase="syncing"
    data-poll-url="{{ route('payments.status', $payment->uuid) }}"
>
    <div class="bf-payment-result bf-store-panel p-8 space-y-4">
        <div data-bf-payment-phase="syncing" class="space-y-4">
            <div class="bf-payment-loader mx-auto"></div>
            <h1 class="font-brand text-2xl text-[var(--bf-ink)]">Confirmando pago…</h1>
            <x-realtime.status-indicator class="justify-center" />
            <p class="text-sm text-[var(--bf-muted)]" data-bf-payment-message>Estamos esperando la confirmación de la entidad financiera.</p>
            <x-payment.status-badge :status="$payment->status" />
            <p class="text-xs text-[var(--bf-muted)]">Referencia: <span data-bf-payment-reference>{{ $payment->reference }}</span></p>
        </div>

        <div data-bf-payment-phase="approved" class="hidden space-y-4">
            <div class="bf-payment-result__icon text-emerald-600 mx-auto">✓</div>
            <h1 class="font-brand text-2xl text-[var(--bf-ink)]">¡Pago aprobado!</h1>
            <p class="text-sm text-[var(--bf-muted)]" data-bf-payment-message>Pago aprobado. Redirigiendo…</p>
            <p class="text-base font-semibold text-[var(--bf-brand)]">Pedido <span data-bf-payment-order></span></p>
        </div>

        <div data-bf-payment-phase="failed" class="hidden space-y-4">
            <div class="bf-payment-result__icon text-red-600 mx-auto">!</div>
            <h1 class="font-brand text-2xl text-[var(--bf-ink)]">Pago no completado</h1>
            <p class="text-sm text-[var(--bf-muted)]" data-bf-payment-message>El pago no pudo confirmarse.</p>
            <a href="{{ route('checkout.show') }}" class="bf-btn-primary w-full justify-center">Reintentar</a>
        </div>

        <div data-bf-payment-phase="connection_error" class="hidden space-y-4">
            <div class="bf-payment-result__icon text-amber-600 mx-auto">?</div>
            <h1 class="font-brand text-2xl text-[var(--bf-ink)]">Confirmación demorada</h1>
            <p class="text-sm text-[var(--bf-muted)]">Aún no recibimos la respuesta final. Revisa tu correo en unos minutos.</p>
            <a href="{{ route('payments.status', $payment->uuid) }}" class="bf-btn-ghost w-full justify-center">Ver estado del pago</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/paymentProcess.js')
@endpush
