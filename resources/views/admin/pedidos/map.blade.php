@extends('layouts.app')

@section('titulo', 'Mapa operativo')
@section('cabecera', 'Mapa en tiempo real')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-4" data-ops-map
     data-map-data-url="{{ route('admin.pedidos.map-data') }}"
     data-api-key="{{ config('services.google.maps_api_key') }}">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-stone-900">Mapa operativo</h1>
            <p class="text-sm text-stone-600">Domiciliarios, pedidos activos y tienda.</p>
        </div>
        <a href="{{ route('admin.pedidos.index') }}" class="bf-btn-ghost">← Pedidos</a>
    </div>
    <div id="ops-map-canvas" class="bf-ops-map-canvas rounded-xl border border-stone-200 overflow-hidden min-h-[420px] bg-stone-100"></div>
    <p class="text-xs text-stone-500">Actualización automática cada 15 s.</p>
</div>
@endsection

@push('scripts')
    @vite('resources/js/operationsMap.js')
@endpush
