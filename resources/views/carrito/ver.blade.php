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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <section class="lg:col-span-2 space-y-4" aria-label="Productos en el carrito">
                <div class="hidden md:grid grid-cols-[minmax(0,1fr)_6rem_5rem_7rem] gap-4 px-4 text-xs font-semibold uppercase tracking-wide text-[var(--bf-muted)]">
                    <span>Producto</span>
                    <span class="text-right">Precio</span>
                    <span class="text-center">Cant.</span>
                    <span class="text-right">Subtotal</span>
                </div>

                @foreach($lineas as $linea)
                    <article class="bg-white rounded-xl border border-[var(--bf-border-brand-subtle)] shadow-sm overflow-hidden">
                        <div class="p-4 flex flex-col gap-4 md:grid md:grid-cols-[minmax(0,1fr)_6rem_5rem_7rem] md:items-center md:gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                @if($linea['imagen_url'])
                                    <img
                                        src="{{ $linea['imagen_url'] }}"
                                        alt="{{ $linea['nombre'] }}"
                                        class="h-20 w-20 shrink-0 rounded-lg object-cover ring-1 ring-black/5"
                                    >
                                @else
                                    <div class="h-20 w-20 shrink-0 rounded-lg bg-stone-100 flex items-center justify-center text-xs text-stone-400 ring-1 ring-black/5">
                                        Sin imagen
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <h2 class="font-semibold text-[var(--bf-ink)] leading-snug">{{ $linea['nombre'] }}</h2>
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
                                    <p class="text-sm text-[var(--bf-muted)] mt-0.5 md:hidden">
                                        @if($isPack)
                                            ${{ number_format($linea['precio'], 0, ',', '.') }}/pack · {{ $qtyDisplay }} {{ $qty === 1.0 ? 'pack' : 'packs' }}
                                        @else
                                            ${{ number_format($linea['precio'], 0, ',', '.') }}/{{ $unitLabel }} · {{ $qtyDisplay }} {{ $unitLabel }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <p class="hidden md:block text-sm text-[var(--bf-muted)] text-right tabular-nums">
                                @if($isPack)
                                    ${{ number_format($linea['precio'], 0, ',', '.') }}/pack
                                @else
                                    ${{ number_format($linea['precio'], 0, ',', '.') }}/{{ $unitLabel }}
                                @endif
                            </p>

                            <p class="hidden md:block text-sm font-medium text-[var(--bf-ink)] text-center tabular-nums">
                                {{ $qtyDisplay }} @if($isPack){{ $qty === 1.0 ? 'pack' : 'packs' }}@else{{ $unitLabel }}@endif
                            </p>

                            <p class="text-base font-semibold text-[var(--bf-brand)] md:text-right tabular-nums">
                                <span class="md:hidden text-sm font-normal text-[var(--bf-muted)] mr-2">Subtotal</span>
                                ${{ number_format($linea['subtotal'], 0, ',', '.') }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="lg:sticky lg:top-24">
                <div class="bf-store-panel p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-[var(--bf-ink)]">Resumen del pedido</h2>

                    <dl class="space-y-3 text-sm">
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
                        Precios según la unidad elegida (kg, lb o pack). El total final se confirma en el siguiente paso.
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
                        <a href="{{ route('checkout.show') }}" class="bf-btn-primary btn-lg w-full justify-center">
                            Continuar al pago
                        </a>
                        <p class="text-xs text-center text-[var(--bf-muted)]">
                            Revisa tu pedido y confirma la compra en el siguiente paso.
                        </p>
                    @endguest
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
