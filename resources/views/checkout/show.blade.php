@extends('layouts.store')

@section('titulo', 'Confirmar compra | BEEF FRESH')

@section('content')
    @php
        $customer = auth()->user();
        $shipping = $customer->snapshotShippingFromProfile();
    @endphp

    <div class="bf-store-page bf-store-page--checkout">
        <nav class="text-xs text-[var(--bf-muted)] mb-2 text-center" aria-label="Ruta">
            <a href="{{ route('products.public.index') }}" class="hover:text-[var(--bf-brand)] hover:underline">Catálogo</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('carrito.ver') }}" class="hover:text-[var(--bf-brand)] hover:underline">Carrito</a>
            <span class="mx-1.5">/</span>
            <span class="text-[var(--bf-ink)]">Pago</span>
        </nav>

        <h1 class="font-brand text-2xl sm:text-3xl text-[var(--bf-ink)] text-center mb-1">Confirmar compra</h1>
        <p class="text-sm text-[var(--bf-muted)] text-center mb-5">Revisa tu pedido y paga de forma segura</p>

        <x-store.checkout-stepper :current="2" />

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="bf-store-panel bf-checkout-sheet">
            <header class="bf-checkout-sheet__head">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--bf-ink)]">Resumen del pedido</h2>
                    <p class="text-xs text-[var(--bf-muted)] mt-0.5">{{ $itemCount }} {{ $itemCount === 1 ? 'línea' : 'líneas' }} · {{ now()->translatedFormat('d M Y') }}</p>
                </div>
            </header>

            <div class="bf-checkout-receipt" role="table" aria-label="Detalle del pedido">
                <div class="bf-checkout-receipt__row bf-checkout-receipt__row--head" role="row">
                    <span role="columnheader">Producto</span>
                    <span role="columnheader" class="text-center">Cant.</span>
                    <span role="columnheader" class="text-right">Subtotal</span>
                </div>
                @foreach($lineas as $linea)
                    @php
                        $unitLabel = $linea['sale_unit']->value;
                        $qty = (float) $linea['cantidad'];
                        $qtyDisplay = fmod($qty, 1.0) === 0.0 ? (string) (int) $qty : number_format($qty, 1, ',', '.');
                    @endphp
                    <div class="bf-checkout-receipt__row" role="row">
                        <div class="min-w-0" role="cell">
                            <p class="font-medium text-[var(--bf-ink)] leading-snug">{{ $linea['nombre'] }}</p>
                            @if(!empty($linea['pricing_label']))
                                <p class="text-[11px] font-semibold mt-0.5 {{ ($linea['pricing_tier'] ?? '') === 'volume' ? 'text-emerald-700' : 'text-[var(--bf-muted)]' }}">{{ $linea['pricing_label'] }}</p>
                            @endif
                        </div>
                        <span class="text-center tabular-nums text-[var(--bf-muted)]" role="cell">{{ $qtyDisplay }} {{ $unitLabel }}</span>
                        <span class="text-right font-medium tabular-nums text-[var(--bf-ink)]" role="cell">${{ number_format($linea['subtotal'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="px-5 sm:px-6 py-3 space-y-1 text-sm border-t border-[var(--bf-border-brand-subtle)]">
                <div class="flex justify-between"><span class="text-[var(--bf-muted)]">Subtotal</span><span class="tabular-nums">${{ number_format($subtotal, 0, ',', '.') }}</span></div>
                @if($shippingFee > 0)
                    <div class="flex justify-between"><span class="text-[var(--bf-muted)]">Envío</span><span class="tabular-nums">${{ number_format($shippingFee, 0, ',', '.') }}</span></div>
                @endif
                @if($discount > 0)
                    <div class="flex justify-between text-emerald-700"><span>Descuento</span><span class="tabular-nums">−${{ number_format($discount, 0, ',', '.') }}</span></div>
                @endif
            </div>

            <div class="bf-checkout-total-band">
                <span class="font-semibold text-[var(--bf-ink)]">Total a pagar</span>
                <span class="text-xl font-bold text-[var(--bf-brand)] tabular-nums">${{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <section class="bf-checkout-section" aria-labelledby="checkout-delivery-title">
                <h3 id="checkout-delivery-title" class="bf-checkout-section__title">Entrega</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Cliente</dt><dd class="font-medium mt-0.5">{{ $shipping['shipping_recipient_name'] }}</dd></div>
                    <div><dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Teléfono</dt><dd class="font-medium mt-0.5">{{ $shipping['shipping_phone'] }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Dirección</dt>
                        <dd class="font-medium mt-0.5 leading-relaxed">
                            {{ $shipping['shipping_address_line1'] }}@if(filled($shipping['shipping_address_line2'])) · {{ $shipping['shipping_address_line2'] }}@endif<br>
                            {{ $shipping['shipping_city'] }}, {{ $shipping['shipping_state'] }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="bf-checkout-section bf-checkout-section--last" aria-labelledby="checkout-payment-title">
                <h3 id="checkout-payment-title" class="bf-checkout-section__title">Pago seguro en línea</h3>

                <div class="bf-payment-trust">
                    <div class="bf-payment-trust__head">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <div>
                            <p class="font-semibold text-[var(--bf-ink)]">Pagar online de forma segura</p>
                            <p class="text-xs text-[var(--bf-muted)] mt-0.5">Paga de forma segura mediante PSE, Nequi, tarjetas débito/crédito y transferencias bancarias.</p>
                        </div>
                    </div>
                    <ul class="bf-payment-methods" aria-label="Métodos de pago disponibles">
                        <li>PSE</li>
                        <li>Nequi</li>
                        <li>Visa</li>
                        <li>MasterCard</li>
                        <li>Bancolombia</li>
                        <li>Davivienda</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('payments.initiate') }}" class="mt-5 space-y-3">
                    @csrf
                    <input type="hidden" name="gateway" value="wompi">
                    <label class="bf-label-muted" for="checkout-notes">Observaciones (opcional)</label>
                    <textarea id="checkout-notes" name="notes" class="bf-textarea min-h-[3rem]" placeholder="Instrucciones para la entrega…"></textarea>
                    <button type="submit" class="bf-btn-primary btn-lg w-full justify-center">
                        Continuar al pago seguro
                    </button>
                </form>

                <p class="mt-4 text-center text-xs text-[var(--bf-muted)]">Procesado por Wompi · Ambiente {{ config('payments.gateways.wompi.sandbox') ? 'sandbox' : 'producción' }}</p>
                <p class="mt-4 text-center">
                    <a href="{{ route('carrito.ver') }}" class="text-sm text-[var(--bf-muted)] hover:text-[var(--bf-brand)] hover:underline">← Volver al carrito</a>
                </p>
            </section>
        </div>
    </div>
@endsection
