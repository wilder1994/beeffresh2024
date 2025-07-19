@extends('layouts.app')

@section('titulo', 'Promociones')
@section('cabecera', 'Lista de Promociones')

@section('contenido')
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Promociones registradas</h2>
            <a href="{{ route('promociones.create') }}" class="btn btn-primary">+ Nueva Promoción</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($promociones as $promocion)
                <div class="bg-white rounded-xl shadow p-4">
                    @if($promocion->imagen)
                        <img src="{{ asset('storage/promociones/' . $promocion->imagen) }}"
                             alt="Imagen promoción"
                             class="w-full h-40 object-cover rounded-lg mb-4">
                    @endif

                    <h3 class="text-xl font-bold text-red-700 mb-2">{{ $promocion->titulo }}</h3>
                    <p class="text-gray-700 text-sm">{{ $promocion->descripcion }}</p>

                    @if($promocion->enlace)
                        <a href="{{ $promocion->enlace }}" target="_blank" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                            Ver más
                        </a>
                    @endif

                    <div class="flex justify-between mt-4">
                        <a href="{{ route('promociones.edit', $promocion) }}" class="btn btn-sm btn-outline">Editar</a>

                        <form action="{{ route('promociones.destroy', $promocion) }}" method="POST"
                              onsubmit="return confirm('¿Estás seguro de eliminar esta promoción?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-error">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <p class="text-gray-600">No hay promociones registradas aún.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
