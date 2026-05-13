@extends('layouts.app')

@section('titulo', 'Cargos')
@section('cabecera', 'Cargos (posiciones)')

@section('contenido')
    <div class="py-4 max-w-4xl mx-auto px-3 sm:px-4 space-y-4">
        @if(session('success'))
            <div class="alert alert-success text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex justify-end">
            <a href="{{ route('admin.positions.create') }}" class="bf-btn-primary">Nuevo cargo</a>
        </div>

        <div class="overflow-x-auto bg-base-100 rounded-lg shadow">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $p)
                        <tr>
                            <td class="font-medium">{{ $p->name }}</td>
                            <td class="font-mono text-xs">{{ $p->slug }}</td>
                            <td><span class="badge badge-sm {{ $p->status === 'active' ? 'badge-success' : 'badge-ghost' }}">{{ $p->status }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.positions.edit', $p) }}" class="link link-primary text-sm">Editar</a>
                                <form method="post" action="{{ route('admin.positions.destroy', $p) }}" class="inline ml-2" onsubmit="return confirm('¿Eliminar este cargo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link text-sm text-error">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-500">No hay cargos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $positions->links() }}
        </div>
    </div>
@endsection
