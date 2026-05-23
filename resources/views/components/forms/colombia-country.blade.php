@props([
    'name' => 'customer_country',
    'id' => null,
])

@php
    use App\Domain\Geo\Colombia;

    $fieldId = $id ?? $name;
    $wireAttrs = $attributes->whereStartsWith('wire:model');
    $hasWire = $wireAttrs->isNotEmpty();
@endphp

<div class="flex flex-col gap-1">
    <p class="bf-input bg-[var(--bf-field-bg)] border border-[var(--bf-border-brand-subtle)] rounded-lg px-2.5 py-2 text-sm text-stone-800 min-h-[2.25rem] flex items-center">
        {{ Colombia::COUNTRY_NAME }} ({{ Colombia::COUNTRY_CODE }})
    </p>
    @if ($hasWire)
        <input type="hidden" {{ $wireAttrs }} value="{{ Colombia::COUNTRY_CODE }}" />
    @else
        <input type="hidden" name="{{ $name }}" id="{{ $fieldId }}" value="{{ Colombia::COUNTRY_CODE }}" />
    @endif
</div>
