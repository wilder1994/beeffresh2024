@props(['slides'])

@if($slides->isNotEmpty())
    @php
        $track = \App\Support\CintaMarqueeSlides::track($slides);
        $duration = \App\Support\CintaMarqueeSlides::animationDurationSeconds($slides);
    @endphp
    <section
        class="bf-cinta-marquee"
        aria-label="Ofertas y destacados"
        style="--bf-cinta-duration: {{ $duration }}s;"
    >
        <div class="bf-cinta-marquee__viewport">
            <div class="bf-cinta-marquee__track">
                @foreach($track as $index => $slide)
                    <article class="bf-cinta-marquee__item">
                        @if($slide->link_url)
                            <a href="{{ $slide->link_url }}" class="bf-cinta-marquee__card">
                                <img
                                    src="{{ $slide->imageUrl() }}"
                                    alt="{{ $slide->alt ?? 'BEEF FRESH' }}"
                                    class="bf-cinta-marquee__img"
                                    @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                                    draggable="false"
                                >
                            </a>
                        @else
                            <div class="bf-cinta-marquee__card">
                                <img
                                    src="{{ $slide->imageUrl() }}"
                                    alt="{{ $slide->alt ?? 'BEEF FRESH' }}"
                                    class="bf-cinta-marquee__img"
                                    @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                                    draggable="false"
                                >
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
