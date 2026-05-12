@extends('layouts.app')

@section('titulo', 'Usuarios')
@section('cabecera', $pageHeading ?? 'Usuarios del sistema')

@section('contenido')
    <div class="py-4 max-w-7xl mx-auto px-3 sm:px-4">
        <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-between items-start sm:items-end mb-4">
            <form method="get" action="{{ $formAction ?? route('admin.users.index') }}" class="flex flex-wrap gap-2 sm:gap-3 items-end">
                @if(($audienceFixed ?? null) === null)
                    <div>
                        <label class="bf-label-muted normal-case">Tipo</label>
                        <select name="audience" class="bf-select min-w-[10rem]" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="clients" @selected(($filters['audience'] ?? '') === 'clients')>Clientes</option>
                            <option value="company" @selected(($filters['audience'] ?? '') === 'company')>Empresa</option>
                            <option value="suppliers" @selected(($filters['audience'] ?? '') === 'suppliers')>Proveedores</option>
                        </select>
                    </div>
                @else
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                            @if(($audienceFixed ?? null) === 'clients')
                                Clientes
                            @elseif(($audienceFixed ?? null) === 'company')
                                Empresa
                            @elseif(($audienceFixed ?? null) === 'suppliers')
                                Proveedores
                            @endif
                        </span>
                        <a href="{{ route('admin.users.index') }}" class="link link-primary text-sm">Ver todos los usuarios</a>
                    </div>
                @endif
                <div>
                    <label class="bf-label-muted normal-case">Buscar</label>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, correo, teléfono…" class="bf-input max-w-xs" />
                </div>
                <button type="submit" class="bf-btn-primary">Filtrar</button>
            </form>
            <a href="{{ route('admin.users.create') }}" class="bf-btn-primary shrink-0">Nuevo usuario</a>
        </div>

        <div class="overflow-x-auto bg-base-100 rounded-lg shadow">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Tipo</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="font-medium">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge badge-ghost badge-sm">{{ $u->role->audienceLabel() }}</span></td>
                            <td>{{ $u->role->label() }}</td>
                            <td>{{ $u->phone ?? '—' }}</td>
                            <td>{{ $u->city ?? '—' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.show', $u) }}" class="link link-primary text-sm">Ver</a>
                                <a href="{{ route('admin.users.edit', $u) }}" class="link text-sm ml-2">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">No hay usuarios con estos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
@endsection
