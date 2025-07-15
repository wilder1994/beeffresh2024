@extends('layouts.app')

@section('titulo', 'Promociones')
@section('cabecera', 'Listado de Promociones')

@section('contenido')
    <div class="max-w-7xl mx-auto p-4">
        <div class="flex justify-end mb-4">
            <a href="{{ route('promociones.create') }}" class="btn btn-primary">+ Nueva Promoción</a>
        </div>

        @if ($promociones->isEmpty())
            <div class="text-center text-gray-500">No hay promociones registradas.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($promociones as $promo)
                    <div class="card bg-base-100 shadow-xl">
                        @if($promo->imagen)
                            <figure><img src="{{ asset('storage/' . $promo->imagen) }}" alt="{{ $promo->titulo }}" /></figure>
                        @endif
                        <div class="card-body">
                            <h2 class="card-title">{{ $promo->titulo }}</h2>
                            <p>{{ Str::limit($promo->descripcion, 100) }}</p>
                            @if($promo->enlace)
                                <a href="{{ $promo->enlace }}" target="_blank" class="text-blue-500 underline">Ver más</a>
                            @endif
                            <div class="card-actions justify-end mt-2">
                                <a href="{{ route('promociones.edit', $promo->id) }}" class="btn btn-sm btn-outline">Editar</a>
                                <form action="{{ route('promociones.destroy', $promo->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline btn-error" onclick="return confirm('Eliminar esta promoción?')">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
