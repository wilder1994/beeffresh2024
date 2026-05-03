@extends('layouts.app')

@section('titulo', 'Domiciliarios')
@section('cabecera', 'Domicilios — ' . auth()->user()->name)

@section('contenido')
    <div class="py-6 max-w-3xl mx-auto px-4">
        <div class="bg-base-200 p-8 rounded-2xl shadow-md text-center space-y-4">
            <h2 class="text-2xl font-bold">Entregas a domicilio</h2>
            <p class="text-gray-700">Aquí se asignarán rutas, estados de entrega en tiempo real y confirmación al cliente.</p>
        </div>
    </div>
@endsection
