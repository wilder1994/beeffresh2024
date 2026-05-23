@props([
    'name' => 'document_type',
    'id' => null,
    'legacyValue' => null,
    'wireLegacyValue' => null,
    'required' => false,
])

@php
    use App\Domain\Users\ColombianDocumentType;

    $fieldId = $id ?? $name;
    $wireAttrs = $attributes->whereStartsWith('wire:model');
    $hasWire = $wireAttrs->isNotEmpty();
    $current = $hasWire ? null : old($name, $legacyValue);
    $legacy = $wireLegacyValue ?? $legacyValue;
    $showLegacy = $legacy !== null && $legacy !== '' && ! ColombianDocumentType::isKnown((string) $legacy);
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
    <option value="">— Seleccionar tipo —</option>
    @foreach (ColombianDocumentType::options() as $code => $label)
        <option value="{{ $code }}" @selected((string) $current === $code)>{{ $label }}</option>
    @endforeach
    @if ($showLegacy)
        <option value="{{ $legacy }}" selected>{{ $legacy }} (registro anterior)</option>
    @endif
</select>
