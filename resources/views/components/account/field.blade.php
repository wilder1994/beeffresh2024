@props(['label', 'value' => null, 'colspan' => false])

<div @class(['min-w-0', 'md:col-span-2' => $colspan])>
    <dt class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">{{ $label }}</dt>
    <dd class="mt-0.5 text-sm text-stone-900 break-words">
        @if(trim((string) ($value ?? '')) !== '')
            {{ $value }}
        @else
            <span class="text-stone-400">—</span>
        @endif
    </dd>
</div>
