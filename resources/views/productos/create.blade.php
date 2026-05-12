@extends('layouts.app')

@section('titulo', 'Crear producto')
@section('cabecera', 'Crear producto')

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

        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">
                <div class="md:col-span-2">
                    <label class="bf-label" for="prod-nombre">Nombre</label>
                    <input id="prod-nombre" type="text" name="nombre" value="{{ old('nombre') }}" class="bf-input" required />
                </div>

                <div class="md:col-span-2">
                    <label class="bf-label" for="prod-desc">Descripción</label>
                    <input id="prod-desc" type="text" name="descripcion" value="{{ old('descripcion') }}" class="bf-input" />
                </div>

                <div>
                    <label class="bf-label" for="prod-precio">Precio</label>
                    <input id="prod-precio" type="number" name="precio" value="{{ old('precio') }}" class="bf-input" step="0.01" required />
                </div>
                <div>
                    <label class="bf-label" for="prod-unidad">Unidad</label>
                    <select id="prod-unidad" name="unidad" class="bf-select" required>
                        <option value="kilo" {{ old('unidad') == 'kilo' ? 'selected' : '' }}>Kilo</option>
                        <option value="libra" {{ old('unidad') == 'libra' ? 'selected' : '' }}>Libra</option>
                    </select>
                </div>

                <div>
                    <label class="bf-label" for="prod-stock">Stock</label>
                    <input id="prod-stock" type="number" name="stock" value="{{ old('stock') }}" class="bf-input" required />
                </div>

                <div class="md:col-span-2">
                    <label class="bf-label" for="prod-promo">Promoción (opcional)</label>
                    <input id="prod-promo" type="text" name="promocion" value="{{ old('promocion') }}" class="bf-input" placeholder="Ej: 10% descuento, 2×1…" />
                </div>

                <div class="md:col-span-2">
                    <label class="bf-label" for="prod-img">Imagen del producto</label>
                    <input id="prod-img" type="file" name="imagen" accept="image/*" class="bf-file" required />
                </div>
            </div>

            <div class="bf-form-actions justify-between">
                <a href="{{ route('productos.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Crear producto</button>
            </div>
        </form>
    </div>
@endsection
