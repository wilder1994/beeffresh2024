@extends('layouts.app')

@section('titulo', 'Registro de pedidos')
@section('cabecera', 'Pedidos — ' . auth()->user()->name)

@section('contenido')
    <div class="py-6 max-w-3xl mx-auto px-4">
        <div class="bg-base-200 p-8 rounded-2xl shadow-md text-center space-y-4">
            <h2 class="text-2xl font-bold">Registro y seguimiento de pedidos</h2>
            <p class="text-gray-700">Aquí se gestionarán los pedidos entrantes (web y presenciales), estados y coordinación con cocina o despacho.</p>
        </div>
    </div>
@endsection
