@php
    $flashSuccess = session('success');
    $flashError = session('error');
    $flashMessage = $flashSuccess ?? $flashError;
    $flashType = $flashSuccess ? 'success' : ($flashError ? 'error' : null);
@endphp

@if($flashMessage && $flashType)
    <div
        class="bf-flash-toast-layer"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        x-data="{ visible: true }"
        x-init="setTimeout(() => { visible = false }, 1400)"
        x-show="visible"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div @class([
            'bf-flash-toast',
            'bf-flash-toast--success' => $flashType === 'success',
            'bf-flash-toast--error' => $flashType === 'error',
        ])>
            <p class="bf-flash-toast__text">{{ $flashMessage }}</p>
        </div>
    </div>
@endif
