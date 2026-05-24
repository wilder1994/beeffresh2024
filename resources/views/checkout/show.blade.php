@extends('layouts.store')

@section('titulo', 'Confirmar compra | BEEF FRESH')

@section('content')
    @php
        $customer = auth()->user();
        $profile = $customer->customerProfile;
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
        <p class="text-sm text-[var(--bf-muted)] text-center mb-5">Revisa tu pedido y confirma la entrega</p>

        <x-store.checkout-stepper :current="2" />

        <div class="bf-store-panel bf-checkout-sheet">
            <header class="bf-checkout-sheet__head">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--bf-ink)]">Resumen del pedido</h2>
                    <p class="text-xs text-[var(--bf-muted)] mt-0.5">
                        {{ $itemCount }} {{ $itemCount === 1 ? 'producto' : 'productos' }}
                        · {{ now()->translatedFormat('d M Y') }}
                    </p>
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
                                <p class="text-[11px] font-semibold mt-0.5 {{ ($linea['pricing_tier'] ?? '') === 'volume' ? 'text-emerald-700' : 'text-[var(--bf-muted)]' }}">
                                    {{ $linea['pricing_label'] }}
                                </p>
                            @endif
                            <p class="text-xs text-[var(--bf-muted)] mt-0.5 tabular-nums">${{ number_format($linea['precio'], 0, ',', '.') }}/{{ $unitLabel }}</p>
                        </div>
                        <span class="text-center tabular-nums text-[var(--bf-muted)]" role="cell">{{ $qtyDisplay }} {{ $unitLabel }}</span>
                        <span class="text-right font-medium tabular-nums text-[var(--bf-ink)]" role="cell">${{ number_format($linea['subtotal'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="bf-checkout-total-band">
                <span class="font-semibold text-[var(--bf-ink)]">Total estimado</span>
                <span class="text-xl font-bold text-[var(--bf-brand)] tabular-nums">${{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <section class="bf-checkout-section" aria-labelledby="checkout-delivery-title">
                <h3 id="checkout-delivery-title" class="bf-checkout-section__title">Entrega</h3>
                <dl class="bf-checkout-delivery grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Cliente</dt>
                        <dd class="font-medium text-[var(--bf-ink)] mt-0.5">{{ $shipping['shipping_recipient_name'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Teléfono</dt>
                        <dd class="font-medium text-[var(--bf-ink)] mt-0.5">{{ $shipping['shipping_phone'] }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Dirección</dt>
                        <dd class="font-medium text-[var(--bf-ink)] mt-0.5 leading-relaxed">
                            {{ $shipping['shipping_address_line1'] }}
                            @if(filled($shipping['shipping_address_line2']))
                                · {{ $shipping['shipping_address_line2'] }}
                            @endif
                            <br>
                            {{ $shipping['shipping_city'] }}, {{ $shipping['shipping_state'] }}
                            @if(filled($shipping['shipping_postal_code']))
                                · {{ $shipping['shipping_postal_code'] }}
                            @endif
                        </dd>
                    </div>
                    @if(filled($shipping['shipping_notes']))
                        <div class="sm:col-span-2">
                            <dt class="text-[var(--bf-muted)] text-xs uppercase tracking-wide">Notas de entrega</dt>
                            <dd class="text-[var(--bf-ink)] mt-0.5">{{ $shipping['shipping_notes'] }}</dd>
                        </div>
                    @endif
                </dl>
                <p class="mt-3">
                    <x-profile.open-button tag="button" class="text-sm font-medium text-[var(--bf-brand)] hover:underline">
                        Editar datos de entrega
                    </x-profile.open-button>
                </p>
            </section>

            <section class="bf-checkout-section bf-checkout-section--last" aria-labelledby="checkout-payment-title">
                <h3 id="checkout-payment-title" class="bf-checkout-section__title">Pago</h3>

                <div class="bf-checkout-notice">
                    <p class="text-sm text-[var(--bf-ink)] leading-relaxed">
                        La pasarela de pago se integrará próximamente. Por ahora puedes confirmar el pedido para descontar stock y registrar la compra.
                    </p>
                </div>

                <form method="POST" action="{{ route('carrito.finalizar') }}" class="mt-5 space-y-3">
                    @csrf
                    <button type="submit" class="bf-btn-primary btn-lg w-full justify-center">
                        Confirmar pedido
                    </button>
                </form>

                <p class="mt-4 text-center">
                    <a href="{{ route('carrito.ver') }}" class="text-sm text-[var(--bf-muted)] hover:text-[var(--bf-brand)] hover:underline">
                        ← Volver al carrito
                    </a>
                </p>
            </section>
        </div>
    </div>
@endsection
