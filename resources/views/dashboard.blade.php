@extends('layouts.app')

@section('titulo','Panel de Administración')
@section('cabecera', 'Bienvenido, ' . auth()->user()->name)

@section('contenido')
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-base-200 p-8 rounded-2xl shadow-md">
                <h2 class="text-3xl font-bold mb-6 text-center">Panel de Administración de BEEF FRESH</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-success w-full text-left p-6 text-lg shadow hover:scale-105 transition">
                        📦 Pedidos en línea
                    </a>
                    <a href="{{ route('productos.index') }}" class="btn btn-primary w-full text-left p-6 text-lg shadow hover:scale-105 transition">
                        🧾 Gestión de Productos
                    </a>
                    <a href="{{ route('videos.index') }}" class="btn btn-secondary w-full text-left p-6 text-lg shadow hover:scale-105 transition">
                        🎥 Gestión de Videos
                    </a>
                    <a href="{{ route('promociones.index') }}" class="btn btn-accent w-full text-left p-6 text-lg shadow hover:scale-105 transition">
                        🎉 Gestión de Promociones
                    </a>
                    <a href="{{ route('cortes.index') }}" class="btn btn-info w-full text-left p-6 text-lg shadow hover:scale-105 transition">
                        🥩 Gestión de Cortes
                    </a>
                    {{-- Puedes seguir agregando más accesos desde aquí si lo necesitas --}}
                </div>
            </div>
        </div>
    </div>
@endsection
