@php
    $tabClass = static fn (bool $active): string => $active
        ? 'border-[var(--bf-brand)] text-[var(--bf-brand)] bg-white shadow-sm'
        : 'border-transparent text-gray-600 hover:text-gray-900 hover:bg-white/70';

    $bundlesTabActive = request()->routeIs(
        'catalog.offers.bundles',
        'catalog.offers.bundles.create',
    ) || (request()->routeIs('catalog.offers.edit') && isset($offer) && $offer->isBundle());

    $volumesTabActive = request()->routeIs(
        'catalog.offers.volumes',
        'catalog.offers.volumes.create',
    ) || (request()->routeIs('catalog.offers.edit') && isset($offer) && $offer->isVolume());
@endphp

<nav class="sticky top-0 z-10 -mx-2 sm:-mx-3 md:-mx-4 px-2 sm:px-3 md:px-4 py-3 mb-4 bg-[var(--bf-cream)]/95 backdrop-blur border-b border-amber-100/80" aria-label="Secciones del catálogo">
    <div class="max-w-7xl mx-auto flex flex-wrap gap-1.5 sm:gap-2">
        <a href="{{ route('catalog.products.index') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass(request()->routeIs('catalog.products.*'))])>
            Productos
        </a>
        <a href="{{ route('catalog.meat-types.index') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass(request()->routeIs('catalog.meat-types.*'))])>
            Tipos de carne
        </a>
        <a href="{{ route('catalog.meat-cuts.index') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass(request()->routeIs('catalog.meat-cuts.*'))])>
            Cortes
        </a>
        <a href="{{ route('catalog.promotions.index') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass(request()->routeIs('catalog.promotions.*'))])>
            Promociones
        </a>
        <a href="{{ route('catalog.offers.bundles') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass($bundlesTabActive)])>
            Combos y packs
        </a>
        <a href="{{ route('catalog.offers.volumes') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass($volumesTabActive)])>
            Escalas por volumen
        </a>
        <a href="{{ route('catalog.pricing.index') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass(request()->routeIs('catalog.pricing.*'))])>
            Precios
        </a>
        <a href="{{ route('catalog.inventory.index') }}" @class(['inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition', $tabClass(request()->routeIs('catalog.inventory.*'))])>
            Inventario
        </a>
    </div>
</nav>
