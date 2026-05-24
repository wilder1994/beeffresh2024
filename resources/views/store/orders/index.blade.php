@extends('layouts.store')

@section('titulo', 'Mis pedidos | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--medium">
    <header class="mb-6">
        <h1 class="font-brand text-2xl sm:text-3xl text-[var(--bf-ink)]">Mis pedidos</h1>
        <p class="text-sm text-[var(--bf-muted)] mt-1">Historial de compras y seguimiento de entregas en curso.</p>
    </header>

    @if($orders->isEmpty())
        <div class="bf-store-panel p-8 text-center space-y-4">
            <p class="text-[var(--bf-muted)]">Aún no tienes pedidos confirmados.</p>
            <a href="{{ route('products.public.index') }}" class="bf-btn-primary inline-flex justify-center">Ir al catálogo</a>
        </div>
    @else
        @if($activeCount > 0)
            <p class="text-sm text-[var(--bf-muted)] mb-4">
                Tienes <strong class="text-[var(--bf-ink)]">{{ $activeCount }}</strong>
                {{ $activeCount === 1 ? 'pedido en curso' : 'pedidos en curso' }}.
            </p>
        @endif

        <div class="space-y-3">
            @foreach($orders as $order)
                <x-store.order-card :order="$order" />
            @endforeach
        </div>

        @if($orders->hasPages())
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
