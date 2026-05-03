@extends('layouts.store')

@section('titulo', 'Mi cuenta · BEEF FRESH')

@section('content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4">
            <p class="text-center text-sm uppercase tracking-widest text-[var(--bf-red)] mb-2">Área cliente</p>
            <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Hola, {{ auth()->user()->name }}</h1>
            <div class="bg-white border border-amber-100/80 p-8 rounded-2xl shadow-md space-y-6">
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
