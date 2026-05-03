@extends('layouts.app')

@section('titulo', 'Caja')
@section('cabecera', 'Caja — ' . auth()->user()->name)

@section('contenido')
    <div class="py-6 max-w-3xl mx-auto px-4">
        <div class="bg-base-200 p-8 rounded-2xl shadow-md text-center space-y-4">
            <h2 class="text-2xl font-bold">Módulo de caja</h2>
            <p class="text-gray-700">Aquí se integrará el cobro en mostrador, cierre de turno y conciliación con pedidos.</p>
        </div>
    </div>
@endsection
