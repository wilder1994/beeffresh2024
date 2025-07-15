@extends('layouts.app')

@section('titulo', 'Listado de productos')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Botón de crear --}}
    <div class="flex justify-end mb-4">
        <a href="{{ route('productos.create') }}" class="btn btn-primary">Nuevo producto</a>
    </div>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Si no hay productos --}}
    @if($productos->isEmpty())
        <div class="text-center text-gray-500 py-10">
            No hay productos aún. Haz clic en "Nuevo producto" para agregar el primero.
        </div>
    @else
        {{-- Grid de productos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($productos as $producto)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition duration-300">
                    <div class="h-48 overflow-hidden rounded-t">
                        <img src="{{ asset('storage/imagenes/' . $producto->imagen) }}"
                             alt="{{ $producto->nombre }}"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold">{{ $producto->nombre }}</h3>
                        <div class="text-sm text-gray-600 mb-2">${{ number_format($producto->precio, 0, ',', '.') }}</div>
                        <p class="text-gray-700 text-sm mb-4">
                            {{ Str::limit($producto->descripcion, 60) }}
                        </p>
                        <div class="flex justify-between">
                            <a href="{{ route('productos.edit', $producto->id) }}"
                               class="btn btn-outline btn-xs">Editar</a>

                            <form action="{{ route('productos.destroy', $producto->id) }}" method="POST"
                                  onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-xs">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
