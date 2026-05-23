@props([
    'action',
    'method' => 'DELETE',
    'blockWhenCount' => 0,
    'blockedMessage' => 'No se puede eliminar.',
    'confirmTitle' => '¿Confirmar eliminación?',
    'confirmMessage' => '',
    'confirmLabel' => 'Eliminar',
    'label' => 'Eliminar',
    'buttonClass' => 'text-sm text-red-600 hover:underline',
])

<div
    x-data="bfDeleteAction(@js([
        'blockWhenCount' => (int) $blockWhenCount,
        'blockedMessage' => $blockedMessage,
        'confirmTitle' => $confirmTitle,
        'confirmMessage' => $confirmMessage,
        'confirmLabel' => $confirmLabel,
    ]))"
    {{ $attributes->class(['inline']) }}
>
    <form x-ref="form" method="POST" action="{{ $action }}" class="inline">
        @csrf
        @method($method)
        <button type="button" class="{{ $buttonClass }}" @click="click()">
            @if($slot->isNotEmpty())
                {{ $slot }}
            @else
                {{ $label }}
            @endif
        </button>
    </form>
</div>
