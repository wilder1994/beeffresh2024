@props([
    'name' => 'customer_state',
    'id' => null,
    'legacyValue' => null,
    'wireLegacyValue' => null,
    'required' => false,
])

@php
    use App\Domain\Geo\ColombianDepartments;

    $fieldId = $id ?? $name;
    $wireAttrs = $attributes->whereStartsWith('wire:model');
    $hasWire = $wireAttrs->isNotEmpty();
    $current = $hasWire ? null : old($name, $legacyValue);
    $legacy = $wireLegacyValue ?? $legacyValue;
    $showLegacy = $legacy !== null && $legacy !== '' && ! ColombianDepartments::isKnown((string) $legacy);
@endphp

<select
    id="{{ $fieldId }}"
    @if (! $hasWire)
        name="{{ $name }}"
    @endif
    {{ $required ? 'required' : '' }}
    {{ $wireAttrs }}
    {{ $attributes->class(['bf-select'])->except(['wire:model', 'wire:model.live', 'wire:model.blur']) }}
>
    <option value="">— Seleccionar departamento —</option>
    @foreach (ColombianDepartments::names() as $department)
        <option value="{{ $department }}" @selected((string) $current === $department)>{{ $department }}</option>
    @endforeach
    @if ($showLegacy)
        <option value="{{ $legacy }}" selected>{{ $legacy }} (registro anterior)</option>
    @endif
</select>
