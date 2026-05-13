@extends('layouts.app')

@php
    use App\Domain\Users\RoleSlug;
    $roleSlug = $user->primaryRoleSlug();
@endphp

@section('titulo', 'Usuario · '.$user->name)
@section('cabecera', $user->name)

@section('contenido')
    <div class="py-6 max-w-4xl mx-auto px-4 space-y-6">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div class="flex items-center gap-4 min-w-0">
                <x-user-avatar :user="$user" size="h-16 w-16" class="ring-2 ring-[var(--bf-red)]/30 shrink-0" />
                <div class="min-w-0 flex flex-wrap gap-2 items-center">
                    @if($roleSlug)
                        <span class="badge badge-lg badge-outline">{{ RoleSlug::label($roleSlug) }}</span>
                        <span class="badge badge-ghost">{{ RoleSlug::audienceLabel($roleSlug) }}</span>
                    @else
                        <span class="badge badge-ghost">Sin rol</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm bg-[var(--bf-red)] text-white border-0">Editar</a>
                <a href="{{ $user->adminUsersListRoute() }}" class="btn btn-sm btn-ghost">Volver al listado</a>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow divide-y">
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div><span class="text-gray-500 text-sm">Correo</span><br>{{ $user->email }}</div>
                <div><span class="text-gray-500 text-sm">Teléfono</span><br>{{ $user->phone ?? '—' }}</div>
                <div><span class="text-gray-500 text-sm">Identificación</span><br>{{ $user->document_number ?? '—' }}</div>
                @if($user->isSupplier() && $user->supplierProfile)
                    <div><span class="text-gray-500 text-sm">Empresa</span><br>{{ $user->supplierProfile->company_name ?? '—' }}</div>
                    <div><span class="text-gray-500 text-sm">NIT</span><br>{{ $user->supplierProfile->nit ?? '—' }}</div>
                @endif
            </div>

            @if($user->isEmployee() && $user->employeeProfile)
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Empleado</h3>
                    <p class="text-sm"><span class="text-gray-500">Cargo:</span> {{ $user->employeeProfile->position?->name ?? '—' }}</p>
                </div>
            @endif

            @if($user->isCustomer() && $user->customerProfile)
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Domicilio de entrega</h3>
                    <p class="text-sm whitespace-pre-line">{{ $user->customerProfile->address ?? '—' }}@if($user->customerProfile->neighborhood)<br>{{ $user->customerProfile->neighborhood }}@endif</p>
                    <p class="text-sm mt-2">{{ $user->customerProfile->city ?? '' }}@if($user->customerProfile->state), {{ $user->customerProfile->state }}@endif @if($user->customerProfile->postal_code) · {{ $user->customerProfile->postal_code }}@endif · {{ $user->customerProfile->country ?? 'DO' }}</p>
                    @if($user->customerProfile->delivery_notes)
                        <p class="text-sm mt-2"><span class="text-gray-500">Indicaciones:</span> {{ $user->customerProfile->delivery_notes }}</p>
                    @endif
                </div>
            @endif

            <div class="p-4 text-xs text-gray-500">
                Registrado {{ $user->created_at->format('d/m/Y H:i') }}
                @if($user->updated_at && $user->updated_at->ne($user->created_at))
                    · Última actualización {{ $user->updated_at->format('d/m/Y H:i') }}
                @endif
            </div>
        </div>
    </div>
@endsection
