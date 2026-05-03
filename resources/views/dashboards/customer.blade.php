@extends('layouts.app')

@section('titulo', 'Mi cuenta')
@section('cabecera', 'Hola, ' . auth()->user()->name)

@section('contenido')
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-base-200 p-8 rounded-2xl shadow-md space-y-6">
                <p class="text-lg text-center text-gray-700">
                    Desde aquí puedes gestionar tu perfil y ver el estado de tus pedidos cuando el módulo esté disponible.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('productos.publico.index') }}" class="btn btn-primary">Ir al catálogo</a>
                    <a href="{{ route('carrito.ver') }}" class="btn btn-outline">Ver carrito</a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-ghost">Mi perfil</a>
                </div>
            </div>
        </div>
    </div>
@endsection
