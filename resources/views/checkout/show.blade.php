@extends('layouts.store')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-6 text-center">Confirmar compra</h2>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">{{ session('error') }}</div>
        @endif

        <div class="space-y-4 mb-8">
            @foreach($lineas as $linea)
                <div class="flex justify-between border-b pb-2">
                    <span>{{ $linea['nombre'] }} × {{ $linea['cantidad'] }}</span>
                    <span>${{ number_format($linea['subtotal'], 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="flex justify-between font-bold text-lg pt-2">
                <span>Total</span>
                <span>${{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </div>

        <p class="text-center text-gray-600 mb-6">
            La pasarela de pago se integrará próximamente. Por ahora puedes confirmar la compra para descontar stock y registrar el pedido.
        </p>

        <form method="POST" action="{{ route('carrito.finalizar') }}" class="text-center">
            @csrf
            <button type="submit" class="btn btn-primary btn-lg">Confirmar compra</button>
        </form>

        <p class="text-center mt-6">
            <a href="{{ route('carrito.ver') }}" class="link link-hover">← Volver al carrito</a>
        </p>
    </div>
@endsection
