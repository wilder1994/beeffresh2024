@extends('layouts.app')

@section('titulo', 'Usuario · '.$user->name)
@section('cabecera', $user->name)

@section('contenido')
    <div class="py-6 max-w-4xl mx-auto px-4 space-y-6">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div class="flex items-center gap-4 min-w-0">
                <x-user-avatar :user="$user" size="h-16 w-16" class="ring-2 ring-[var(--bf-red)]/30 shrink-0" />
                <div class="min-w-0">
                    <span class="badge badge-lg badge-outline">{{ $user->role->label() }}</span>
                    <span class="badge badge-ghost ml-2">{{ $user->role->audienceLabel() }}</span>
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
                @if($user->isSupplier())
                    <div><span class="text-gray-500 text-sm">Empresa</span><br>{{ $user->company_name ?? '—' }}</div>
                @endif
            </div>

            @if($user->isCustomer())
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Domicilio de entrega</h3>
                    <p class="text-sm whitespace-pre-line">{{ $user->address_line1 ?? '—' }}@if($user->address_line2)<br>{{ $user->address_line2 }}@endif</p>
                    <p class="text-sm mt-2">{{ $user->city ?? '' }}@if($user->state), {{ $user->state }}@endif @if($user->postal_code) · {{ $user->postal_code }}@endif · {{ $user->country ?? 'DO' }}</p>
                    @if($user->delivery_instructions)
                        <p class="text-sm mt-2"><span class="text-gray-500">Indicaciones:</span> {{ $user->delivery_instructions }}</p>
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
