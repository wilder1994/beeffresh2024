@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'linkUrl' => null,
    'linkLabel' => null,
])

<header class="bf-home-section-head">
    @if($eyebrow)
        <p class="bf-home-section-head__eyebrow">{{ $eyebrow }}</p>
    @endif
    <div class="bf-home-section-head__row">
        <div class="min-w-0">
            <h2 class="bf-home-section-head__title">{{ $title }}</h2>
            @if($subtitle)
                <p class="bf-home-section-head__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if($linkUrl && $linkLabel)
            <a href="{{ $linkUrl }}" class="bf-home-section-head__link shrink-0">{{ $linkLabel }}</a>
        @endif
    </div>
</header>
