@extends('layouts.app')

@section('titulo', 'Gestión de Cortes')
@section('cabecera', 'Listado de Cortes de Carne')

@section('contenido')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-end mb-4">
            <a href="{{ route('cortes.create') }}" class="btn btn-success">➕ Nuevo Corte</a>
        </div>

        @if($cortes->isEmpty())
            <div class="text-center text-gray-500">
                No hay cortes registrados aún. Haz clic en "Nuevo Corte" para agregar uno.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($cortes as $corte)
                    <div class="card bg-base-100 shadow-xl">
                        @if($corte->imagen)
                            <figure>
                                <img src="{{ asset('storage/cortes/' . $corte->imagen) }}" alt="{{ $corte->nombre }}" class="h-48 object-cover w-full">
                            </figure>
                        @endif
                        <div class="card-body">
                            <h2 class="card-title">{{ $corte->nombre }}</h2>
                            <p>{{ Str::limit($corte->descripcion, 60) }}</p>
                            <div class="card-actions justify-end mt-4">
                                <a href="{{ route('cortes.edit', $corte) }}" class="btn btn-sm btn-outline">✏️ Editar</a>
                                <form action="{{ route('cortes.destroy', $corte) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este corte?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline btn-error">🗑️ Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
