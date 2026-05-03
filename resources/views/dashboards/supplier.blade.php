@extends('layouts.supplier')

@section('titulo', 'Portal proveedores')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-2xl font-semibold text-slate-800 mb-2">Bienvenido, {{ auth()->user()->name }}</h2>
        <p class="text-slate-600 mb-6">
            Este espacio está pensado para la relación comercial con proveedores (órdenes de compra, documentos y estado de entregas).
            El módulo se irá completando sin afectar la tienda ni el panel interno de la carnicería.
        </p>
        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-600">
            <strong class="text-slate-800">Próximos pasos sugeridos:</strong> catálogo de suministros, solicitudes y calendario de entregas.
        </div>
    </div>
@endsection
