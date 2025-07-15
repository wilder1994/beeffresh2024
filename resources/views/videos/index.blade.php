@extends('layouts.app')

@section('titulo', 'Gestión de Videos')
@section('cabecera', 'Videos de la Página Principal')

@section('contenido')
<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Listado de Videos</h2>
        <a href="{{ route('videos.create') }}" class="btn btn-primary">➕ Nuevo Video</a>
    </div>

    @if($videos->isEmpty())
        <p class="text-gray-500">Aún no hay videos cargados.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($videos as $video)
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title">{{ $video->titulo }}</h3>

                        @if($video->tipo === 'youtube')
                            <iframe class="w-full aspect-video" src="{{ $video->url }}" frameborder="0" allowfullscreen></iframe>
                        @elseif($video->tipo === 'archivo')
                            <video controls class="w-full">
                                <source src="{{ asset('storage/videos/' . $video->archivo) }}" type="video/mp4">
                                Tu navegador no soporta este video.
                            </video>
                        @endif

                        <div class="card-actions justify-end mt-4">
                            <a href="{{ route('videos.edit', $video->id) }}" class="btn btn-outline btn-sm">✏️ Editar</a>
                            <form action="{{ route('videos.destroy', $video->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este video?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-error btn-sm">🗑️ Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
