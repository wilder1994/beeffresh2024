<td class="text-right whitespace-nowrap">
    <div class="bf-catalog-actions">
        <a href="{{ route('catalog.offers.edit', $offer) }}" class="bf-catalog-action" title="Editar" aria-label="Editar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bf-catalog-action__icon" aria-hidden="true">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
            </svg>
        </a>
        <x-bf.delete-action
            :action="route('catalog.offers.destroy', $offer)"
            confirm-title="¿Eliminar oferta?"
            :confirm-message="'Se eliminará «'.$offer->name.'».'"
            button-class="bf-catalog-action bf-catalog-action--danger"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bf-catalog-action__icon" aria-hidden="true">
                <path d="M3 6h18"/>
                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6"/>
                <path d="M14 11v6"/>
            </svg>
            <span class="sr-only">Eliminar</span>
        </x-bf.delete-action>
    </div>
</td>
