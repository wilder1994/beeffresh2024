@extends('layouts.app')

@php
    use App\Domain\Users\RoleSlug;
@endphp

@section('titulo', 'Usuarios')
@section('cabecera', $pageHeading ?? 'Usuarios del sistema')

@section('contenido')
    <div class="py-4 max-w-7xl mx-auto px-3 sm:px-4">
        <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-between items-start sm:items-end mb-4">
            <form method="get" action="{{ $formAction ?? route('admin.users.index') }}" class="bf-form-toolbar flex flex-wrap gap-2 sm:gap-3 items-end">
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
            <button
                type="button"
                class="bf-btn-primary shrink-0"
                onclick="window.Livewire && Livewire.dispatch('open-user-account', { mode: 'create' })"
            >Nuevo usuario</button>
        </div>

        <div class="bf-table-panel">
            <table class="bf-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Tipo</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php($slug = $u->primaryRoleSlug())
                        <tr>
                            <td class="font-medium text-stone-900">{{ $u->name }}</td>
                            <td class="text-stone-700">{{ $u->email }}</td>
                            <td>
                                <span class="badge badge-sm border border-stone-200 bg-stone-100 text-stone-700">
                                    {{ $slug ? RoleSlug::audienceLabel($slug) : '—' }}
                                </span>
                            </td>
                            <td class="text-stone-800">{{ $slug ? RoleSlug::label($slug) : '—' }}</td>
                            <td class="text-stone-700">{{ $u->phone ?? '—' }}</td>
                            <td class="text-stone-700">{{ $u->primaryCityForList() ?? '—' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    class="text-sm font-medium text-[var(--bf-brand)] hover:underline"
                                    onclick="window.Livewire && Livewire.dispatch('open-user-account', { mode: 'view', userId: {{ $u->id }} })"
                                >Ver</button>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-stone-600 hover:text-[var(--bf-brand)] hover:underline ml-3"
                                    onclick="window.Livewire && Livewire.dispatch('open-user-account', { mode: 'edit', userId: {{ $u->id }} })"
                                >Editar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-stone-500">No hay usuarios con estos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>

    <livewire:admin.user-account-modal />
@endsection
