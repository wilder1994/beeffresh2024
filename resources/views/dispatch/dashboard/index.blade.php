@extends('layouts.app')

@push('bf-realtime-meta')
    <meta name="bf-staff-operations" content="1">
    <meta name="bf-dispatcher-id" content="{{ auth()->id() }}">
@endpush

@section('titulo', 'Mi panel de despacho')
@section('cabecera', 'Panel despachador')

@section('contenido')
<div
    class="max-w-7xl mx-auto space-y-4"
    data-dispatcher-dashboard
    data-feed-url="{{ route('dispatch.dashboard.feed') }}"
>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 px-0 sm:px-1">
        <div>
            <p class="text-[10px] sm:text-xs uppercase tracking-widest text-[var(--bf-red)] font-semibold">Operaciones</p>
            <h1 class="text-xl sm:text-2xl font-bold text-stone-900">Mi panel de despacho</h1>
            <p class="text-xs sm:text-sm text-stone-600 mt-0.5">{{ $dispatcher['name'] ?? auth()->user()->name }} · pedidos asignados a ti</p>
            <x-realtime.status-indicator class="mt-2" />
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.pedidos.index') }}" class="bf-btn-primary text-sm">Mis pedidos</a>
            <a href="{{ route('admin.pedidos.map') }}" class="bf-btn-ghost text-sm">Mapa</a>
        </div>
    </div>

    <x-ops.dashboard-analytics
        :kpi="$kpi ?? []"
        :analytics="$analytics ?? []"
        :show-revenue="false"
        :show-ranking="false"
        :recent-orders="$recent_orders ?? collect()"
    />
</div>
@endsection

@push('scripts')
    @vite(['resources/js/dispatcherDashboard.js'])
@endpush
