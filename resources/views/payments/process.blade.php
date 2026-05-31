@extends('layouts.store')

@section('titulo', 'Procesando pago | BEEF FRESH')

@push('bf-realtime-meta')
    @auth
        <meta name="bf-payment-uuid" content="{{ $payment->uuid }}">
    @endauth
@endpush

@section('content')
<div
    class="bf-store-page bf-store-page--checkout max-w-lg mx-auto"
    data-bf-payment-process
    data-phase="opening"
    data-poll-url="{{ route('payments.status', $payment->uuid) }}"
    data-auto-open-widget="1"
    data-widget-config='@json($widget->widgetConfig)'
>
    <h1 class="font-brand text-2xl text-[var(--bf-ink)] text-center mb-2">Completa tu pago</h1>
    <p class="text-sm text-[var(--bf-muted)] text-center mb-6">
        Referencia <span class="font-mono text-xs" data-bf-payment-reference>{{ $payment->reference }}</span>
        · ${{ number_format((float) $payment->amount, 0, ',', '.') }} COP
    </p>
    <x-realtime.status-indicator class="justify-center mb-4" />

    <div class="bf-store-panel p-6 text-center space-y-4">
        {{-- Abriendo widget --}}
        <div data-bf-payment-phase="opening" class="space-y-4">
            <div class="bf-payment-loader mx-auto" aria-hidden="true"></div>
            <p class="text-sm text-[var(--bf-muted)]">Abriendo pasarela segura…</p>
            <p class="text-xs text-amber-800 hidden" data-bf-payment-opening-hint></p>
            <button type="button" data-bf-wompi-open class="bf-btn-primary w-full justify-center">Pagar ahora</button>
            <p class="text-xs text-[var(--bf-muted)]">Si no se abre automáticamente, pulsa «Pagar ahora».</p>
        </div>

        {{-- Confirmando post-widget --}}
        <div data-bf-payment-phase="syncing" class="hidden space-y-4">
            <div class="bf-payment-loader mx-auto" aria-hidden="true"></div>
            <h2 class="text-lg font-semibold text-[var(--bf-ink)]">Confirmando tu pago</h2>
            <p class="text-sm text-[var(--bf-muted)]" data-bf-payment-message>Estamos verificando el resultado con Wompi…</p>
            <p class="text-xs text-[var(--bf-muted)]">No cierres esta ventana.</p>
        </div>

        {{-- Aprobado --}}
        <div data-bf-payment-phase="approved" class="hidden space-y-4">
            <div class="bf-payment-result__icon text-emerald-600 mx-auto">✓</div>
            <h2 class="text-lg font-semibold text-[var(--bf-ink)]">¡Pago aprobado!</h2>
            <p class="text-sm text-[var(--bf-muted)]" data-bf-payment-message>Tu pedido fue confirmado.</p>
            <p class="text-base font-semibold text-[var(--bf-brand)]">Pedido <span data-bf-payment-order></span></p>
            <a href="#" data-bf-payment-tracking-link class="bf-btn-primary w-full justify-center hidden">Ver seguimiento</a>
            <a href="#" data-bf-payment-catalog-link class="text-sm text-[var(--bf-muted)] hover:underline block">Volver al catálogo</a>
        </div>

        {{-- Rechazado / fallido --}}
        <div data-bf-payment-phase="failed" class="hidden space-y-4">
            <div class="bf-payment-result__icon text-red-600 mx-auto">!</div>
            <h2 class="text-lg font-semibold text-[var(--bf-ink)]">No se completó el pago</h2>
            <p class="text-sm text-[var(--bf-muted)]" data-bf-payment-message>El pago no pudo confirmarse.</p>
            <a href="{{ route('checkout.show') }}" class="bf-btn-primary w-full justify-center">Reintentar</a>
            <a href="{{ route('carrito.ver') }}" class="text-sm text-[var(--bf-muted)] hover:underline">Volver al carrito</a>
        </div>

        {{-- Error de conexión / timeout --}}
        <div data-bf-payment-phase="connection_error" class="hidden space-y-4">
            <div class="bf-payment-result__icon text-amber-600 mx-auto">?</div>
            <h2 class="text-lg font-semibold text-[var(--bf-ink)]">No pudimos confirmar el estado</h2>
            <p class="text-sm text-[var(--bf-muted)]">Revisa tu correo o el estado del pago en unos segundos.</p>
            <a href="{{ route('payments.status', $payment->uuid) }}" class="bf-btn-primary w-full justify-center">Ver estado del pago</a>
            <button type="button" data-bf-wompi-open class="bf-btn-ghost w-full justify-center">Abrir pasarela de nuevo</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script id="bf-wompi-widget-script" src="{{ $widget->widgetScriptUrl }}" async></script>
@vite('resources/js/paymentProcess.js')
@endpush
