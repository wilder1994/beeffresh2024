@extends('layouts.app')

@push('bf-realtime-meta')
    <meta name="bf-staff-operations" content="1">
@endpush

@section('titulo', 'Panel ejecutivo')
@section('cabecera', 'Centro de mando · Ejecutivo')

@section('contenido')
@php $kpi = $kpi ?? []; @endphp
<div
    class="max-w-7xl mx-auto space-y-4"
    data-executive-dashboard
    data-feed-url="{{ route('admin.dashboard.feed') }}"
>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 px-0 sm:px-1">
        <div>
            <p class="text-[10px] sm:text-xs uppercase tracking-widest text-[var(--bf-red)] font-semibold">Administración</p>
            <h1 class="text-xl sm:text-2xl font-bold text-stone-900">Panel ejecutivo</h1>
            <p class="text-xs sm:text-sm text-stone-600 mt-0.5">{{ now()->translatedFormat('l j \d\e F') }} · visión global</p>
            <x-realtime.status-indicator class="mt-2" />
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.pedidos.index') }}" class="bf-btn-primary text-sm">Pedidos</a>
            <a href="{{ route('admin.pedidos.map') }}" class="bf-btn-ghost text-sm">Mapa</a>
        </div>
    </div>

    <x-ops.dashboard-analytics
        :kpi="$kpi"
        :analytics="$analytics ?? []"
        :show-revenue="true"
        :show-ranking="true"
        :recent-orders="$recent_orders ?? collect()"
        :low-stock="$low_stock ?? collect()"
    />
</div>
@endsection

@push('scripts')
    @vite(['resources/js/executiveDashboard.js'])
@endpush
