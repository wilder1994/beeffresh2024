@extends('layouts.app')

@section('titulo', 'Usuarios')
@section('cabecera', 'Usuarios del sistema')

@section('contenido')
    <div class="py-6 max-w-7xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-end mb-6">
            <form method="get" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="label py-0"><span class="label-text text-xs">Tipo</span></label>
                    <select name="audience" class="select select-bordered select-sm bg-white" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="clients" @selected(($filters['audience'] ?? '') === 'clients')>Clientes</option>
                        <option value="company" @selected(($filters['audience'] ?? '') === 'company')>Empresa</option>
                        <option value="suppliers" @selected(($filters['audience'] ?? '') === 'suppliers')>Proveedores</option>
                    </select>
                </div>
                <div>
                    <label class="label py-0"><span class="label-text text-xs">Buscar</span></label>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, correo, teléfono…" class="input input-bordered input-sm bg-white w-full max-w-xs" />
                </div>
                <button type="submit" class="btn btn-sm bg-[var(--bf-red)] text-white border-0">Filtrar</button>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm bg-[var(--bf-red)] text-white border-0">Nuevo usuario</a>
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
