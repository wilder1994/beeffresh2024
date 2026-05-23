@extends('layouts.app')

@section('titulo', 'Banners · Inicio')
@section('cabecera', 'Banners del inicio')

@section('contenido')
    <div class="max-w-4xl mx-auto px-3 py-4">
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-600">Promociones visuales en la página de inicio (`/`).</p>
            <a href="{{ route('admin.store.banners.create') }}" class="bf-btn-primary">Nuevo banner</a>
        </div>
        <div class="bf-table-panel">
            <table class="bf-table">
                <thead><tr><th>Título</th><th>Orden</th><th>Activo</th><th></th></tr></thead>
                <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td>{{ $banner->title }}</td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>{{ $banner->is_active ? 'Sí' : 'No' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.store.banners.edit', $banner) }}" class="text-sm text-[var(--bf-brand)] hover:underline mr-2">Editar</a>
                                <x-bf.delete-action
                                    :action="route('admin.store.banners.destroy', $banner)"
                                    confirm-title="¿Eliminar banner?"
                                    :confirm-message="'Se eliminará «'.$banner->title.'».'"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 text-gray-500">Sin banners.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
