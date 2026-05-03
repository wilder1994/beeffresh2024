@extends('layouts.store')

@section('titulo', 'Nosotros · BEEF FRESH')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8 md:py-12">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-brand text-[var(--bf-brand)] text-center mb-8 md:mb-10">Nosotros</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-10 mb-12">
            <article class="bg-white/90 rounded-2xl border border-amber-100/80 shadow-sm p-6 md:p-8">
                <h2 class="text-lg md:text-xl font-semibold text-[var(--bf-ink)] mb-3">{{ $profile->about_heading }}</h2>
                <div class="text-sm md:text-base text-[var(--bf-muted)] leading-relaxed whitespace-pre-line">{{ $profile->about_content }}</div>
            </article>
            <article class="bg-white/90 rounded-2xl border border-amber-100/80 shadow-sm p-6 md:p-8">
                <h2 class="text-lg md:text-xl font-semibold text-[var(--bf-ink)] mb-3">{{ $profile->promise_heading }}</h2>
                <div class="text-sm md:text-base text-[var(--bf-muted)] leading-relaxed whitespace-pre-line">{{ $profile->promise_content }}</div>
            </article>
        </div>

        <section class="bg-white/90 rounded-2xl border border-amber-100/80 shadow-sm p-6 md:p-8 text-center">
            <h2 class="text-lg md:text-xl font-semibold text-[var(--bf-ink)] mb-4">{{ $profile->social_heading }}</h2>
            @php
                $links = array_filter([
                    'Facebook' => $profile->social_facebook,
                    'Instagram' => $profile->social_instagram,
                    'X / Twitter' => $profile->social_twitter,
                    'YouTube' => $profile->social_youtube,
                ]);
            @endphp
            @if(count($links) > 0)
                <ul class="flex flex-wrap justify-center gap-4 md:gap-6">
                    @foreach($links as $label => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm md:text-base font-medium text-[var(--bf-brand)] hover:underline">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-[var(--bf-muted)]">Las redes sociales se pueden configurar desde el panel de administración.</p>
            @endif
        </section>

        <p class="mt-10 text-center">
            <a href="{{ route('home') }}" class="text-sm text-[var(--bf-brand)] font-medium hover:underline">← Volver al inicio</a>
        </p>
    </div>
@endsection
