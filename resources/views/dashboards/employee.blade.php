@extends('layouts.app')

@section('titulo', 'Panel empleado')
@section('cabecera', 'Panel empleado')

@section('contenido')
    <div class="max-w-3xl mx-auto bf-form-panel">
        <p class="text-stone-700 text-sm leading-relaxed">
            Hola, <strong>{{ auth()->user()->name }}</strong>. Tu cuenta tiene rol <strong>Empleado</strong>
            @if(auth()->user()->employeeProfile?->position)
                · cargo <strong>{{ auth()->user()->employeeProfile->position->name }}</strong>
            @endif
            . Usa el menú lateral para acceder a los módulos que te hayan habilitado.
        </p>
        <p class="text-xs text-stone-500 mt-4">Si necesitas más permisos, solicítalos al administrador.</p>
    </div>
@endsection
