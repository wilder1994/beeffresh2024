@extends('layouts.store')

@section('titulo', 'Inicio | BEEF FRESH · Tienda')

@section('content')
    <x-store.cinta-carousel :slides="$cintaSlides" />

<div class="bg-white py-10 px-6 md:px-16">
    <section class="mb-16">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Recetas en Video</h2>

        @if($videos->isEmpty())
            <p class="text-gray-500">Próximamente compartiremos nuestras recetas más deliciosas.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($videos as $video)
                    <div class="aspect-w-16 aspect-h-9">
                        @if($video->tipo === 'youtube')
                            @php
                                $ytSrc = $video->url;
                                $ytEmbedOk = is_string($ytSrc) && str_starts_with($ytSrc, 'https://www.youtube.com/embed/');
                            @endphp
                            @if($ytEmbedOk)
                                <iframe class="rounded-xl w-full h-full" src="{{ $ytSrc }}" title="{{ $video->titulo }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            @else
                                <p class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-gray-600">Este video no se puede mostrar (enlace no válido).</p>
                            @endif
                        @elseif($video->tipo === 'archivo')
                            <video controls class="rounded-xl w-full h-full">
                                <source src="{{ asset('storage/videos/' . $video->archivo) }}" type="video/mp4">
                                Tu navegador no soporta este video.
                            </video>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mb-16">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Promociones del Mes</h2>
        @if ($banners->isEmpty())
            <p class="text-gray-500">No hay promociones activas en este momento.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($banners as $banner)
                    <div class="p-6 bg-red-50 rounded-xl shadow hover:shadow-md">
                        <h3 class="text-xl font-bold text-red-700 mb-2">{{ $banner->title }}</h3>
                        <p class="text-gray-700 mb-2">{{ $banner->description }}</p>
                        @if ($banner->imageUrl())
                            <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="mt-4 rounded-xl shadow w-full h-40 object-cover">
                        @endif
                        @if ($banner->link)
                            <a href="{{ $banner->link }}" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Ver más</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mb-16">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tipos de Cortes</h2>

        @if($highlights->isEmpty())
            <p class="text-gray-500">No hay cortes disponibles en este momento.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                @foreach($highlights as $highlight)
                    <div class="text-center">
                        @if($highlight->imageUrl())
                            <img src="{{ $highlight->imageUrl() }}" alt="{{ $highlight->title }}" class="rounded-xl shadow-md w-full h-40 object-cover">
                        @else
                            <div class="rounded-xl shadow-md w-full h-40 bg-stone-100 flex items-center justify-center text-stone-400 text-sm">{{ $highlight->title }}</div>
                        @endif
                        <p class="mt-2 font-semibold text-gray-700">{{ $highlight->title }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-12 mb-4 text-center px-4">
        <a href="{{ route('nosotros') }}" class="inline-flex items-center justify-center text-[var(--bf-brand)] font-semibold hover:underline text-base md:text-lg">
            Conoce más sobre nosotros
        </a>
    </section>

</div>
@endsection
