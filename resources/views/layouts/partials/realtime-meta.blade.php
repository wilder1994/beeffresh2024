{{-- Metadatos BF-Realtime (Fase 0). No sustituye polling. --}}
@if(auth()->check())
    <meta name="bf-user-id" content="{{ auth()->id() }}">
    @if(auth()->user()->canAccessOrderOperations() || auth()->user()->isDispatcher())
        <meta name="bf-staff-operations" content="1">
    @endif
@endif
@stack('bf-realtime-meta')
