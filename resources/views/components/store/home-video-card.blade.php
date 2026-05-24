@props([
    'title',
    'featured' => false,
    'isYoutube' => false,
    'embedUrl' => null,
    'thumbnailUrl' => null,
    'fileUrl' => null,
])

@php
    $sizeClass = $featured ? 'bf-home-video-card--featured' : 'bf-home-video-card--compact';
@endphp

<article
    class="bf-home-video-card {{ $sizeClass }}"
    x-data="{
        playing: false,
        embedBase: @js($embedUrl),
        fileUrl: @js($fileUrl),
        get iframeSrc() {
            if (!this.embedBase) return '';
            return this.embedBase + (this.embedBase.includes('?') ? '&' : '?') + 'autoplay=1';
        }
    }"
>
    <div class="bf-home-video-card__frame">
        <template x-if="!playing">
            <button type="button" class="bf-home-video-card__poster" @click="playing = true" aria-label="Reproducir {{ $title }}">
                @if($isYoutube && $thumbnailUrl)
                    <img src="{{ $thumbnailUrl }}" alt="" class="bf-home-video-card__thumb" loading="lazy">
                @elseif($fileUrl)
                    <video class="bf-home-video-card__thumb" muted playsinline preload="metadata">
                        <source src="{{ $fileUrl }}" type="video/mp4">
                    </video>
                @else
                    <div class="bf-home-video-card__thumb bf-home-video-card__thumb--empty"></div>
                @endif
                <span class="bf-home-video-card__play" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-7 w-7 fill-current"><path d="M8 5v14l11-7z"/></svg>
                </span>
            </button>
        </template>
        <template x-if="playing">
            <div class="bf-home-video-card__player">
                @if($isYoutube && $embedUrl)
                    <iframe
                        class="bf-home-video-card__iframe"
                        :src="iframeSrc"
                        title="{{ $title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                    ></iframe>
                @elseif($fileUrl)
                    <video controls autoplay class="bf-home-video-card__iframe">
                        <source src="{{ $fileUrl }}" type="video/mp4">
                    </video>
                @endif
            </div>
        </template>
    </div>
    <h3 class="bf-home-video-card__title">{{ $title }}</h3>
</article>
