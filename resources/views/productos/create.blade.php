@extends('layouts.app')

@section('titulo', 'Crear producto')

@section('contenido')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="w-full max-w-2xl bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Crear nuevo producto</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nombre --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}"
                       class="w-full input input-bordered" required />
            </div>

            {{-- Descripción --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                       class="w-full input input-bordered" />
            </div>

            {{-- Precio --}}
            {{-- Precio --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Precio</label>
                <div class="flex gap-4">
                    <input type="number" name="precio" value="{{ old('precio') }}"
                        class="w-full input input-bordered" step="0.01" required />

                    <select name="unidad" class="input input-bordered" required>
                        <option value="kilo" {{ old('unidad') == 'kilo' ? 'selected' : '' }}>Kilo</option>
                        <option value="libra" {{ old('unidad') == 'libra' ? 'selected' : '' }}>Libra</option>
                    </select>
                </div>
            </div>


            {{-- Stock --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Stock</label>
                <input type="number" name="stock" value="{{ old('stock') }}"
                       class="w-full input input-bordered" required />
            </div>

            {{-- Promoción (opcional) --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Promoción (opcional)</label>
                <input type="text" name="promocion" value="{{ old('promocion') }}"
                    class="w-full input input-bordered"
                    placeholder="Ej: 10% de descuento, precio especial $14.000..." />
            </div>

            {{-- Imagen --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Imagen del producto</label>
                <input type="file" name="imagen" accept="image/*"
                       class="file-input file-input-bordered w-full" required />
            </div>

            {{-- Botones --}}
            <div class="flex justify-between">
                <a href="{{ route('productos.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear producto</button>
            </div>
        </form>
    </div>
</div>
@endsection
