@extends('layouts.store')

@section('titulo', 'Carrito | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--wide">
    <nav class="text-xs text-[var(--bf-muted)] mb-2" aria-label="Ruta">
        <a href="{{ route('products.public.index') }}" class="hover:text-[var(--bf-brand)] hover:underline">Catálogo</a>
        <span class="mx-1.5">/</span>
        <span class="text-[var(--bf-ink)]">Carrito</span>
    </nav>

    <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="font-brand text-2xl sm:text-3xl text-[var(--bf-ink)] leading-tight">Tu carrito</h1>
            @if($itemCount > 0)
                <p class="text-sm text-[var(--bf-muted)] mt-0.5">{{ $itemCount }} {{ $itemCount === 1 ? 'producto' : 'productos' }}</p>
            @endif
        </div>
        <a href="{{ route('products.public.index') }}" class="bf-btn-ghost shrink-0 self-start sm:self-center">
            ← Seguir comprando
        </a>
    </header>

    @if($lineas === [])
        <div class="bf-store-panel max-w-lg mx-auto text-center py-12 px-6">
            <span class="text-5xl leading-none select-none" aria-hidden="true">🛒</span>
            <h2 class="text-xl font-semibold text-[var(--bf-ink)] mt-4">Tu carrito está vacío</h2>
            <p class="text-sm text-[var(--bf-muted)] mt-2">Explora el catálogo y agrega carnes frescas a tu pedido.</p>
            <a href="{{ route('products.public.index') }}" class="bf-btn-primary mt-6 inline-flex">
                Ver catálogo
            </a>
        </div>
    @else
        <div data-cart-page data-cart-validate-url="{{ route('carrito.validar') }}" class="space-y-4">
            <div class="hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                 data-cart-validate-banner role="status"></div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <section class="lg:col-span-2" aria-label="Productos en el carrito">
                <div class="bf-cart-panel">
                    <div class="bf-cart-table__head hidden md:grid" aria-hidden="true">
                        <span>Producto</span>
                        <span class="text-right">Precio</span>
                        <span class="text-center">Cantidad</span>
                        <span class="text-right">Subtotal</span>
                    </div>

                    <div class="bf-cart-table__body">
                        @foreach($lineas as $linea)
                            <x-store.cart-line-row :linea="$linea" />
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="lg:sticky lg:top-24">
                <div class="bf-store-panel p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-[var(--bf-ink)]">Resumen del pedido</h2>

                    <ul class="bf-cart-summary-lines space-y-2" aria-label="Desglose por línea">
                        @foreach($lineas as $linea)
                            @php
                                $unitLabel = $linea['sale_unit'] instanceof \App\Domain\Catalog\StockUnit
                                    ? $linea['sale_unit']->value
                                    : (string) $linea['sale_unit'];
                                $qty = (float) $linea['cantidad'];
                                $isPack = $unitLabel === 'pack';
                                $qtyDisplay = $isPack
                                    ? (string) (int) $qty
                                    : (fmod($qty, 1.0) === 0.0 ? (string) (int) $qty : number_format($qty, 1, ',', '.'));
                            @endphp
                            <li class="bf-cart-summary-line">
                                <span class="bf-cart-summary-line__name">
                                    {{ $linea['nombre'] }}
                                    <span class="bf-cart-summary-line__qty">· {{ $qtyDisplay }} {{ $isPack ? ($qty === 1.0 ? 'pack' : 'packs') : $unitLabel }}</span>
                                </span>
                                <span class="bf-cart-summary-line__amount tabular-nums">${{ number_format($linea['subtotal'], 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="space-y-3 text-sm pt-3 border-t border-[var(--bf-border-brand-subtle)]">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-[var(--bf-muted)]">Productos ({{ $itemCount }})</dt>
                            <dd class="font-medium text-[var(--bf-ink)] tabular-nums">${{ number_format($total, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 pt-3 border-t border-[var(--bf-border-brand-subtle)]">
                            <dt class="text-base font-semibold text-[var(--bf-ink)]">Total estimado</dt>
                            <dd class="text-xl font-bold text-[var(--bf-brand)] tabular-nums">${{ number_format($total, 0, ',', '.') }}</dd>
                        </div>
                    </dl>

                    <p class="text-xs text-[var(--bf-muted)] leading-relaxed">
                        Ajusta cantidades arriba; los precios se recalculan según promos y ofertas por volumen.
                    </p>

                    @guest
                        <div class="rounded-lg border border-[var(--bf-border-brand-subtle)] bg-white/60 px-4 py-4 space-y-3">
                            <p class="text-sm text-[var(--bf-ink)] font-medium">Para pagar necesitas una cuenta de cliente.</p>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="bf-btn-primary w-full justify-center">
                                    Ingresar como cliente
                                </a>
                                <x-auth.register-button label="Registrarse como cliente" class="w-full justify-center" />
                            </div>
                        </div>
                    @else
                        <a href="{{ route('checkout.show') }}"
                           data-cart-checkout
                           class="bf-btn-primary btn-lg w-full justify-center">
                            Continuar al pago
                        </a>
                        <p class="text-xs text-center text-[var(--bf-muted)]">
                            Revisa tu pedido y confirma la compra en el siguiente paso.
                        </p>
                    @endguest
                </div>
            </aside>
        </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    @vite('resources/js/cartValidate.js')
@endpush
