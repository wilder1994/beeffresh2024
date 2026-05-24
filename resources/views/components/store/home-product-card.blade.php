@props([
    'url',
    'imageUrl',
    'title',
    'badge' => null,
    'priceLabel' => null,
    'referencePrice' => null,
    'availabilityLabel' => null,
    'meta' => null,
])

<a href="{{ $url }}" class="bf-home-product-card group">
    <div class="bf-home-product-card__media">
        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="bf-home-product-card__img" loading="lazy">
        @if($badge)
            <span class="bf-home-product-card__badge">{{ $badge }}</span>
        @endif
        @if($availabilityLabel)
            <span class="bf-home-product-card__stock">{{ $availabilityLabel }}</span>
        @endif
    </div>
    <div class="bf-home-product-card__body">
        <h3 class="bf-home-product-card__title">{{ $title }}</h3>
        @if($meta)
            <p class="bf-home-product-card__meta">{{ $meta }}</p>
        @endif
        <div class="bf-home-product-card__prices">
            @if($referencePrice && $priceLabel && $referencePrice > $priceLabel)
                <p class="bf-home-product-card__ref">${{ number_format($referencePrice, 0, ',', '.') }}</p>
            @endif
            @if($priceLabel !== null)
                <p class="bf-home-product-card__price">${{ number_format($priceLabel, 0, ',', '.') }}</p>
            @endif
        </div>
        <span class="bf-home-product-card__cta">Ver producto →</span>
    </div>
</a>
