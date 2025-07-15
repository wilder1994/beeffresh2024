@extends('layouts.app')

@section('titulo', 'Editar producto')

@section('contenido')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="w-full max-w-2xl bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Editar producto</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}"
                       class="w-full input input-bordered" required />
            </div>

            {{-- Descripción --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $producto->descripcion) }}"
                       class="w-full input input-bordered" />
            </div>

            {{-- Precio --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Precio</label>
                <input type="number" name="precio" value="{{ old('precio', $producto->precio) }}"
                       class="w-full input input-bordered" required />
            </div>

            {{-- Stock --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Stock</label>
                <input type="number" name="stock" value="{{ old('stock', $producto->stock) }}"
                       class="w-full input input-bordered" required />
            </div>

            {{-- Imagen actual --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Imagen actual</label>
                @if($producto->imagen)
                    <img src="{{ asset('storage/imagenes/' . $producto->imagen) }}"
                         alt="Imagen del producto"
                         class="w-32 h-32 object-cover rounded border" />
                @else
                    <p class="text-gray-500">No hay imagen cargada.</p>
                @endif
            </div>

            {{-- Nueva imagen --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Cambiar imagen (opcional)</label>
                <input type="file" name="imagen" accept="image/*"
                       class="file-input file-input-bordered w-full" />
            </div>

            {{-- Botones --}}
            <div class="flex justify-between">
                <a href="{{ route('productos.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar producto</button>
            </div>
        </form>
    </div>
</div>
@endsection
