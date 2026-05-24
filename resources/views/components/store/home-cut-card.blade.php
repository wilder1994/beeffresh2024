@props([
    'name',
    'meta' => null,
    'imageUrl' => null,
    'productsCount' => 0,
    'catalogUrl',
])

<a href="{{ $catalogUrl }}" class="bf-home-cut-card group">
    <div class="bf-home-cut-card__media">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $name }}" class="bf-home-cut-card__img" loading="lazy">
        @else
            <div class="bf-home-cut-card__placeholder">{{ $name }}</div>
        @endif
    </div>
    <div class="bf-home-cut-card__body">
        <h3 class="bf-home-cut-card__title">{{ $name }}</h3>
        @if($meta)
            <p class="bf-home-cut-card__meta">{{ $meta }}</p>
        @endif
        @if($productsCount > 0)
            <p class="bf-home-cut-card__count">{{ $productsCount }} {{ $productsCount === 1 ? 'producto' : 'productos' }}</p>
        @endif
    </div>
</a>
