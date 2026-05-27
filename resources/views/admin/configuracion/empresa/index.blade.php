@extends('layouts.app')

@section('titulo', 'Configuración · Empresa')
@section('cabecera', 'Empresa y marca')

@php
    $tabs = [
        'general' => ['label' => 'General', 'route' => route('admin.configuracion.empresa', ['tab' => 'general'])],
        'ubicacion' => ['label' => 'Ubicación', 'route' => route('admin.configuracion.empresa', ['tab' => 'ubicacion'])],
        'nosotros' => ['label' => 'Nosotros', 'route' => route('admin.configuracion.empresa', ['tab' => 'nosotros'])],
    ];
@endphp

@section('contenido')
<div class="max-w-4xl mx-auto px-3 sm:px-4 py-4 space-y-4">
    <p class="text-sm text-[var(--bf-muted)] leading-snug">
        Configura la identidad de BEEF FRESH, la sede operativa (mapa y despacho) y el contenido público de
        <a href="{{ route('nosotros') }}" target="_blank" rel="noopener" class="font-medium text-[var(--bf-brand)] hover:underline">/nosotros</a>.
    </p>

    <nav class="flex flex-wrap gap-1 p-1 rounded-xl border border-stone-200/90 bg-white/80 shadow-sm" aria-label="Secciones de configuración">
        @foreach($tabs as $key => $meta)
            <a
                href="{{ $meta['route'] }}"
                @class([
                    'flex-1 min-w-[5.5rem] text-center px-3 py-2 rounded-lg text-sm font-medium transition',
                    $tab === $key
                        ? 'bg-[var(--bf-brand)] text-white shadow-sm'
                        : 'text-stone-600 hover:bg-stone-100',
                ])
                @if($tab === $key) aria-current="page" @endif
            >
                {{ $meta['label'] }}
            </a>
        @endforeach
    </nav>

    @if($tab === 'general')
        @include('admin.configuracion.empresa.partials.tab-general', ['profile' => $profile, 'logoUrl' => $logoUrl])
    @elseif($tab === 'ubicacion')
        @include('admin.configuracion.empresa.partials.tab-ubicacion', ['profile' => $profile])
    @else
        @include('admin.configuracion.empresa.partials.tab-nosotros', ['profile' => $profile])
    @endif
</div>
@endsection
