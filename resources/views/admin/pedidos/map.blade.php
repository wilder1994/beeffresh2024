@extends('layouts.app')

@section('titulo', 'Mapa operativo')
@section('staff_map_page', '1')
@section('cabecera_compact', '1')
@section('cabecera', 'Mapa operativo')

@push('bf-realtime-meta')
    <meta name="bf-staff-operations-map" content="1">
@endpush

@section('contenido')
<div
    class="bf-ops-map-shell max-w-7xl mx-auto w-full"
    data-ops-map
    data-map-data-url="{{ route('admin.pedidos.map-data') }}"
    data-api-key="{{ config('services.google.maps_api_key') }}"
>
    <div class="bf-ops-map-toolbar flex flex-wrap items-center justify-between gap-2 shrink-0">
        <p class="text-sm text-stone-600">
            Domiciliarios en vivo (GPS cada ~12 s en ruta), pedidos activos y tienda. Requiere panel domiciliario abierto + Reverb.
        </p>
        <div class="flex flex-wrap items-center gap-3">
            <x-realtime.status-indicator />
            <a href="{{ route('admin.pedidos.index') }}" class="bf-btn-ghost shrink-0">← Pedidos</a>
        </div>
    </div>
    <div id="ops-map-canvas" class="bf-ops-map-canvas bf-ops-map-canvas--fill rounded-xl border border-stone-200 overflow-hidden bg-stone-100"></div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/operationsMap.js')
@endpush
