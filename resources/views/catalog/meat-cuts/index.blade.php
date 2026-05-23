@extends('catalog.layout')

@section('catalogTitle', 'Cortes · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Cortes</h1>
            <p class="text-sm text-gray-600">Cortes comerciales vinculados a cada tipo de carne.</p>
        </div>
        <button type="button" class="bf-btn-primary" onclick="document.getElementById('meat-cut-modal').showModal()">Nuevo corte</button>
    </div>

    <div class="bf-table-panel">
        <table class="bf-table">
            <thead>
                <tr>
                    <th>Corte</th>
                    <th>Tipo</th>
                    <th>Productos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($meatCuts as $cut)
                    <tr>
                        <td>{{ $cut->name }}</td>
                        <td>{{ $cut->meatType?->name }}</td>
                        <td>{{ $cut->products_count }}</td>
                        <td>{{ $cut->status->label() }}</td>
                        <td class="text-right">
                            <x-bf.delete-action
                                :action="route('catalog.meat-cuts.destroy', $cut)"
                                :block-when-count="$cut->products_count"
                                blocked-message="No se puede eliminar: hay productos con este corte."
                                confirm-title="¿Eliminar corte?"
                                :confirm-message="'Se eliminará «'.$cut->name.'».'"
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-gray-500">Sin cortes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <dialog id="meat-cut-modal" class="rounded-xl p-0 w-full max-w-md backdrop:bg-black/40">
        <form method="POST" action="{{ route('catalog.meat-cuts.store') }}" class="bf-form-panel m-0 space-y-3">
            @csrf
            <h2 class="text-lg font-semibold">Nuevo corte</h2>
            <div>
                <label class="bf-label" for="mc-type">Tipo de carne</label>
                <select id="mc-type" name="meat_type_id" class="bf-select" required>
                    @foreach($meatTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="bf-label" for="mc-name">Nombre</label>
                <input id="mc-name" type="text" name="name" class="bf-input" required>
            </div>
            <div>
                <label class="bf-label" for="mc-desc">Descripción</label>
                <textarea id="mc-desc" name="description" class="bf-textarea" rows="2"></textarea>
            </div>
            <div>
                <label class="bf-label" for="mc-status">Estado</label>
                <select id="mc-status" name="status" class="bf-select">
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
