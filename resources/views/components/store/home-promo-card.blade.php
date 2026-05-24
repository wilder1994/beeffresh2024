@props(['banner', 'featured' => false])

@php
    $href = $banner->link ?: route('products.public.index');
    $sizeClass = $featured ? 'bf-home-promo-card--featured' : 'bf-home-promo-card--compact';
@endphp

<article class="bf-home-promo-card {{ $sizeClass }}">
    <a href="{{ $href }}" @if($banner->link) target="_blank" rel="noopener" @endif class="bf-home-promo-card__link">
        <div class="bf-home-promo-card__media">
            @if($banner->imageUrl())
                <img
                    src="{{ $banner->imageUrl() }}"
                    alt="{{ $banner->title }}"
                    class="bf-home-promo-card__img"
                    loading="lazy"
                >
            @else
                <div class="bf-home-promo-card__placeholder" aria-hidden="true"></div>
            @endif
            <div class="bf-home-promo-card__scrim"></div>
            <div class="bf-home-promo-card__body">
                <h3 class="bf-home-promo-card__title">{{ $banner->title }}</h3>
                @if($banner->description)
                    <p class="bf-home-promo-card__desc">{{ $banner->description }}</p>
                @endif
                <span class="bf-home-promo-card__cta">Ver oferta</span>
            </div>
        </div>
    </a>
</article>
