@extends('layouts.app')

@section('titulo', 'Destacados · Inicio')
@section('cabecera', 'Destacados del inicio')

@section('contenido')
    <div class="max-w-4xl mx-auto px-3 py-4">
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-600">Galería «Tipos de cortes» en la página de inicio.</p>
            <a href="{{ route('admin.store.highlights.create') }}" class="bf-btn-primary">Nuevo destacado</a>
        </div>
        <div class="bf-table-panel">
            <table class="bf-table">
                <thead><tr><th>Título</th><th>Orden</th><th>Activo</th><th></th></tr></thead>
                <tbody>
                    @forelse($highlights as $highlight)
                        <tr>
                            <td>{{ $highlight->title }}</td>
                            <td>{{ $highlight->sort_order }}</td>
                            <td>{{ $highlight->is_active ? 'Sí' : 'No' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.store.highlights.edit', $highlight) }}" class="text-sm text-[var(--bf-brand)] hover:underline mr-2">Editar</a>
                                <x-bf.delete-action
                                    :action="route('admin.store.highlights.destroy', $highlight)"
                                    confirm-title="¿Eliminar destacado?"
                                    :confirm-message="'Se eliminará «'.$highlight->title.'».'"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 text-gray-500">Sin destacados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
