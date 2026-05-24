@props(['tiles'])

@if($tiles->isNotEmpty())
    @php
        $track = \App\Support\CintaMarqueeSlides::track($tiles);
        $duration = \App\Support\CintaMarqueeSlides::animationDurationSeconds($tiles);
    @endphp
    <section
        class="bf-cinta-marquee"
        aria-label="Destacados de la tienda"
        style="--bf-cinta-duration: {{ $duration }}s;"
    >
        <div class="bf-cinta-marquee__viewport">
            <div class="bf-cinta-marquee__track">
                @foreach($track as $tile)
                    <article class="bf-cinta-marquee__item">
                        <a href="{{ $tile->url }}" class="bf-cinta-marquee__card bf-cinta-tile">
                            <img
                                src="{{ $tile->imageUrl }}"
                                alt="{{ $tile->title }}"
                                class="bf-cinta-marquee__img"
                                loading="lazy"
                                draggable="false"
                            >
                            <div class="bf-cinta-tile__overlay">
                                <span class="bf-cinta-tile__badge">{{ $tile->badge }}</span>
                                <p class="bf-cinta-tile__title">{{ $tile->title }}</p>
                                @if($tile->priceLabel)
                                    <p class="bf-cinta-tile__price">{{ $tile->priceLabel }}</p>
                                @endif
                                @if($tile->availabilityLabel)
                                    <p class="bf-cinta-tile__stock">{{ $tile->availabilityLabel }}</p>
                                @endif
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
