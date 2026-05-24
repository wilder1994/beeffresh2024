@props(['status'])

@php
    $enum = $status instanceof \App\Enums\OrderStatus ? $status : \App\Enums\OrderStatus::from((string) $status);
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold', $enum->badgeClass()]) }}>
    {{ $enum->label() }}
</span>
