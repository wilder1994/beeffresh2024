@extends('layouts.app')

@section('titulo', 'Recetas')
@section('cabecera', 'Lista de recetas')

@section('contenido')
<div class="flex justify-end m-4">
    <a href="{{ route('recetas.create') }}" class="btn btn-primary">Nueva receta</a>
</div>

@if ($recetas->isEmpty())
    <div class="text-center text-gray-500">No hay recetas registradas aún.</div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        @foreach($recetas as $receta)
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h2 class="card-title">{{ $receta->titulo }}</h2>
                @if ($receta->tipo === 'youtube' && $receta->url)
                    <iframe class="w-full h-48" src="{{ $receta->url }}" frameborder="0" allowfullscreen></iframe>
                @elseif ($receta->tipo === 'archivo' && $receta->archivo)
                    <video class="w-full h-48" controls>
                        <source src="{{ asset('storage/recetas/' . $receta->archivo) }}" type="video/mp4">
                        Tu navegador no soporta la reproducción de video.
                    </video>
                @endif
                <div class="card-actions justify-end">
                    <a href="{{ route('recetas.edit', $receta->id) }}" class="btn btn-outline btn-sm">Editar</a>
                    <form action="{{ route('recetas.destroy', $receta->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta receta?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
