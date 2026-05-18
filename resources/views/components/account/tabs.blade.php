@props(['tabs' => []])

<nav class="flex flex-wrap gap-1 border-b border-stone-200 -mb-px" role="tablist" aria-label="Secciones">
    @foreach($tabs as $tab)
        <button
            type="button"
            role="tab"
            @if(isset($tab['id']))
                @click="tab = @js($tab['id'])"
                :aria-selected="tab === @js($tab['id'])"
                :class="tab === @js($tab['id']) ? 'border-[var(--bf-brand)] text-[var(--bf-brand)] bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 hover:border-stone-300'"
            @endif
            class="px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 transition-colors rounded-t-md"
        >
            {{ $tab['label'] }}
        </button>
    @endforeach
</nav>
