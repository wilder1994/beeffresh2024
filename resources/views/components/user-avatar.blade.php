@props(['user', 'size' => 'h-10 w-10'])
@php
    $url = $user->avatarUrl();
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1, 'UTF-8'));
@endphp
@if($url)
    <img {{ $attributes->merge(['class' => $size.' rounded-full object-cover']) }} src="{{ $url }}" alt="" />
@else
    <div {{ $attributes->merge(['class' => $size.' rounded-full flex items-center justify-center text-sm font-bold text-white bg-white/20 ring-2 ring-white/30']) }}>{{ $initial }}</div>
@endif
