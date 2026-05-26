{{-- Metadatos BF-Realtime (Fase 0–1.5). Polling sigue como fallback. --}}
@if(auth()->check())
    <meta name="bf-user-id" content="{{ auth()->id() }}">
    @if(auth()->user()->canAccessOrderOperations() || auth()->user()->isDispatcher() || auth()->user()->isAdmin())
        <meta name="bf-staff-operations" content="1">
        <meta name="bf-realtime-health-url" content="{{ route('admin.realtime.health') }}">
    @endif
    @if(auth()->user()->can(\App\Domain\Users\PermissionKey::MODULE_INVENTORY))
        <meta name="bf-staff-inventory" content="1">
    @endif
@endif
@stack('bf-realtime-meta')
