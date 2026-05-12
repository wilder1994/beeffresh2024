@extends('layouts.app')

@section('titulo', 'Editar producto')
@section('cabecera', 'Editar producto')

@section('contenido')
    <div class="max-w-3xl mx-auto px-3 sm:px-4 py-4">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">
                <div class="md:col-span-2">
                    <label class="bf-label" for="ep-nombre">Nombre</label>
                    <input id="ep-nombre" type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="bf-input" required />
                </div>

                <div class="md:col-span-2">
                    <label class="bf-label" for="ep-desc">Descripción</label>
                    <input id="ep-desc" type="text" name="descripcion" value="{{ old('descripcion', $producto->descripcion) }}" class="bf-input" />
                </div>

                <div>
                    <label class="bf-label" for="ep-precio">Precio</label>
                    <input id="ep-precio" type="number" name="precio" value="{{ old('precio', $producto->precio) }}" class="bf-input" required />
                </div>

                <div>
                    <label class="bf-label" for="ep-unidad">Unidad de medida</label>
                    <select id="ep-unidad" name="unidad" class="bf-select" required>
                        <option value="libra" {{ old('unidad', $producto->unidad) == 'libra' ? 'selected' : '' }}>Libra</option>
                        <option value="kilo" {{ old('unidad', $producto->unidad) == 'kilo' ? 'selected' : '' }}>Kilo</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="bf-label" for="ep-promo">Promoción (opcional)</label>
                    <input id="ep-promo" type="text" name="promocion" value="{{ old('promocion', $producto->promocion) }}" class="bf-input" placeholder="Ej: 2×1, 10% descuento…" />
                </div>

                <div>
                    <label class="bf-label" for="ep-stock">Stock</label>
                    <input id="ep-stock" type="number" name="stock" value="{{ old('stock', $producto->stock) }}" class="bf-input" required />
                </div>

                <div class="md:col-span-2">
                    <span class="bf-label normal-case">Imagen actual</span>
                    @if($producto->imagen)
                        <img src="{{ asset('storage/imagenes/' . $producto->imagen) }}" alt="" class="w-28 h-28 object-cover rounded-lg border border-stone-200 mt-1" />
                    @else
                        <p class="text-xs text-stone-500 mt-1">Sin imagen.</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="bf-label" for="ep-img">Cambiar imagen (opcional)</label>
                    <input id="ep-img" type="file" name="imagen" accept="image/*" class="bf-file" />
                </div>
            </div>

            <div class="bf-form-actions justify-between">
                <a href="{{ route('productos.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Actualizar producto</button>
            </div>
        </form>
    </div>
@endsection
