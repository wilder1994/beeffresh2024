@props([
    'offer',
    'referencePrice',
    'offerPrice',
    'unitSuffix' => null,
    'volumeSummary' => null,
    'available',
    'label',
])

<a href="{{ $offer->isVolume() && $offer->product ? route('products.public.show', $offer->product) : route('offers.public.show', $offer) }}" class="bf-home-product-card group">
    <div class="bf-home-product-card__media">
        <img src="{{ $offer->imageUrl() }}" alt="{{ $offer->name }}" class="bf-home-product-card__img" loading="lazy">
        <span class="bf-home-product-card__badge">{{ $offer->isVolume() ? 'Por cantidad' : 'Pack' }}</span>
        @if($label)
            <span class="bf-home-product-card__stock">{{ $label }}</span>
        @endif
    </div>
    <div class="bf-home-product-card__body">
        <h3 class="bf-home-product-card__title">{{ $offer->name }}</h3>
        @if($offer->isVolume() && $volumeSummary)
            <p class="bf-home-product-card__meta">{{ $volumeSummary }}</p>
        @elseif($offer->description)
            <p class="bf-home-product-card__meta">{{ Str::limit($offer->description, 70) }}</p>
        @endif
        <div class="bf-home-product-card__prices">
            @if($referencePrice > $offerPrice)
                <p class="bf-home-product-card__ref">
                    ${{ number_format($referencePrice, 0, ',', '.') }}@if($unitSuffix)<span class="text-[10px] font-normal opacity-80">{{ $unitSuffix }}</span>@endif
                </p>
            @endif
            <p class="bf-home-product-card__price">
                ${{ number_format($offerPrice, 0, ',', '.') }}@if($unitSuffix)<span class="text-xs font-semibold opacity-90">{{ $unitSuffix }}</span>@endif
            </p>
        </div>
        <span class="bf-home-product-card__cta">Ver oferta →</span>
    </div>
</a>
