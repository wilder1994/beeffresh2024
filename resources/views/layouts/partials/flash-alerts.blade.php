@php
    $flashSuccess = session('success');
    $flashError = session('error');
@endphp

<div
    class="bf-flash-toast-layer"
    role="status"
    aria-live="polite"
    aria-atomic="true"
    x-data
    x-init="
        @if($flashSuccess)
            $store.bfToast.show('success', @js($flashSuccess));
        @elseif($flashError)
            $store.bfToast.show('error', @js($flashError));
        @endif
    "
    x-show="$store.bfToast.visible"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
>
    <div
        class="bf-flash-toast"
        :class="$store.bfToast.type === 'error' ? 'bf-flash-toast--error' : 'bf-flash-toast--success'"
    >
        <p class="bf-flash-toast__text" x-text="$store.bfToast.message"></p>
    </div>
</div>
