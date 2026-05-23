@extends('layouts.store')

@section('titulo', 'Nosotros · BEEF FRESH')

@section('content')
    <div class="bf-store-page bf-store-page--medium">
        <h1 class="text-2xl sm:text-3xl font-brand text-[var(--bf-brand)] text-center mb-5 md:mb-6">Nosotros</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-8 md:mb-10">
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
            <h2 class="text-lg md:text-xl font-semibold text-[var(--bf-ink)] mb-5">{{ $profile->social_heading }}</h2>
            <x-store.social-icons :profile="$profile" />
        </section>
    </div>
@endsection
