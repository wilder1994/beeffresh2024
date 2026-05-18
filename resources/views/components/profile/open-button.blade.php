@props([
    'tag' => 'button',
    'class' => '',
])

@php
    $baseClass = trim('cursor-pointer text-gray-900 '.$class);
    $alpineOpen = "window.bfOpenProfileModal && window.bfOpenProfileModal(); const d = \$el.closest('details'); if (d) { d.removeAttribute('open'); }";
    $jsOpen = "event.preventDefault(); window.bfOpenProfileModal && window.bfOpenProfileModal(); var d = this.closest('details'); if (d) { d.removeAttribute('open'); }";
@endphp

@if($tag === 'a')
    <a
        href="{{ route('profile.edit') }}"
        {{ $attributes->merge(['class' => $baseClass]) }}
        x-on:click.prevent.stop="{{ $alpineOpen }}"
        onclick="{{ $jsOpen }}"
    >{{ $slot }}</a>
@else
    <button
        type="button"
        {{ $attributes->merge(['class' => $baseClass]) }}
        x-on:click.stop="{{ $alpineOpen }}"
        onclick="{{ $jsOpen }}"
    >{{ $slot }}</button>
@endif
