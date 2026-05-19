@props([
    'variant' => 'store',
    'label' => 'Registrarse',
])

@php
    $classes = match ($variant) {
        'gold' => 'btn btn-sm bg-[var(--bf-gold)] text-[var(--bf-rust-deep)] border-0 hover:brightness-105',
        'primary' => 'bf-btn-primary btn-sm',
        default => 'btn btn-sm bg-[var(--bf-gold)] text-[var(--bf-rust-deep)] border-0 hover:brightness-105',
    };
    $openJs = "event.preventDefault(); window.bfOpenRegisterConfirm && window.bfOpenRegisterConfirm();";
@endphp

<button
    type="button"
    {{ $attributes->merge(['class' => $classes]) }}
    x-on:click.prevent="window.bfOpenRegisterConfirm && window.bfOpenRegisterConfirm()"
    onclick="{{ $openJs }}"
>
    {{ $label }}
</button>
