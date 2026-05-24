@props([
    'tone' => 'cream',
])

@php
    $toneClass = $tone === 'white' ? 'bf-home-section--white' : 'bf-home-section--cream';
@endphp

<section {{ $attributes->merge(['class' => "bf-home-section {$toneClass}"]) }}>
    <div class="bf-home-section__inner">
        {{ $slot }}
    </div>
</section>
