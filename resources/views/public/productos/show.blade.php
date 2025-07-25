@extends('layouts.guest')

@section('titulo', $producto->nombre)
@section('contenido')
    <h2 class="text-2xl font-bold mb-4">{{ $producto->nombre }}</h2>
    <p class="mb-2">Precio: ${{ number_format($producto->precio, 0, ',', '.') }}</p>
    <p class="mb-4">{{ $producto->descripcion }}</p>

    @auth
        <form method="POST" action="{{ route('carrito.agregar', $producto->id) }}">
            @csrf
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Agregar al carrito</button>
        </form>
    @else
        <a href="{{ route('login') }}" class="text-blue-600 underline">Inicia sesión para comprar</a>
    @endauth
@endsection
