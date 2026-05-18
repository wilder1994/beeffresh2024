@props([
    'name',
    'show' => false,
    'maxWidth' => '4xl',
    'zIndex' => 'z-[55]',
])

@php
$maxWidthClass = match ($maxWidth) {
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    default => 'sm:max-w-4xl',
};
$initialShow = $show || session('open_profile_modal');
@endphp

<div
    x-data="{ show: @js((bool) $initialShow) }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="
        const d = $event.detail;
        const id = typeof d === 'string' ? d : (d && d.id ? d.id : null);
        if (id === @js($name)) { show = true }
    "
    x-on:close-modal.window="
        const d = $event.detail;
        const id = typeof d === 'string' ? d : (d && d.id ? d.id : null);
        if (id === @js($name)) { show = false }
    "
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 overflow-y-auto px-3 py-6 sm:px-4 {{ $zIndex }}"
    role="dialog"
    aria-modal="true"
    :aria-hidden="!show"
>
    <div
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 bg-stone-900/50"
        x-on:click="show = false"
        aria-hidden="true"
    ></div>

    <div class="flex min-h-full items-start sm:items-center justify-center pointer-events-none">
        <div
            x-show="show"
            x-transition
            x-on:click.stop
            {{ $attributes->merge(['class' => "pointer-events-auto relative w-full {$maxWidthClass} max-h-[min(90vh,52rem)] flex flex-col"]) }}
        >
            {{ $slot }}
        </div>
    </div>
</div>
