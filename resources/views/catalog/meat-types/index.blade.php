@extends('catalog.layout')

@section('catalogTitle', 'Tipos de carne · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tipos de carne</h1>
            <p class="text-sm text-gray-600">Taxonomía principal del catálogo.</p>
        </div>
        <button type="button" class="bf-btn-primary" onclick="document.getElementById('meat-type-modal').showModal()">Nuevo tipo</button>
    </div>

    <div class="bf-table-panel">
        <table class="bf-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Cortes</th>
                    <th>Productos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($meatTypes as $type)
                    <tr>
                        <td>
                            @if($type->color)
                                <span class="inline-block w-3 h-3 rounded-full mr-2 align-middle" style="background: {{ $type->color }}"></span>
                            @endif
                            {{ $type->name }}
                        </td>
                        <td class="font-mono text-xs">{{ $type->slug }}</td>
                        <td>{{ $type->meat_cuts_count }}</td>
                        <td>{{ $type->products_count }}</td>
                        <td>{{ $type->status->label() }}</td>
                        <td class="text-right">
                            <x-bf.delete-action
                                :action="route('catalog.meat-types.destroy', $type)"
                                :block-when-count="$type->products_count"
                                blocked-message="No se puede eliminar: hay productos con este tipo."
                                confirm-title="¿Eliminar tipo de carne?"
                                :confirm-message="'Se eliminará «'.$type->name.'» y sus cortes asociados.'"
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">Sin tipos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <dialog id="meat-type-modal" class="rounded-xl p-0 w-full max-w-md backdrop:bg-black/40">
        <form method="POST" action="{{ route('catalog.meat-types.store') }}" class="bf-form-panel m-0 space-y-3">
            @csrf
            <h2 class="text-lg font-semibold">Nuevo tipo de carne</h2>
            <div>
                <label class="bf-label" for="mt-name">Nombre</label>
                <input id="mt-name" type="text" name="name" class="bf-input" required>
            </div>
            <div>
                <label class="bf-label" for="mt-color">Color</label>
                <input id="mt-color" type="color" name="color" class="bf-input h-10">
            </div>
            <div>
                <label class="bf-label" for="mt-status">Estado</label>
                <select id="mt-status" name="status" class="bf-select">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>
            <div class="bf-form-actions">
                <button type="button" class="bf-btn-ghost" onclick="this.closest('dialog').close()">Cancelar</button>
                <button type="submit" class="bf-btn-primary">Guardar</button>
            </div>
        </form>
    </dialog>
@endsection
