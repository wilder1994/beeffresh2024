@extends('layouts.app')

@section('titulo', 'Inicio | BEEF FRESH')

@section('contenido')
<div class="bg-white py-10 px-6 md:px-16">
    {{-- Encabezado principal de bienvenida --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-red-600 mb-4">Bienvenido a BEEF FRESH</h1>
        <p class="text-lg text-gray-600">Carnes frescas y de calidad directamente a tu mesa.</p>
    </div>

    {{-- Sección: Videos Promocionales o de recetas --}}
    <section class="mb-16">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Recetas en Video</h2>

        @if($videos->isEmpty())
            <p class="text-gray-500">Próximamente compartiremos nuestras recetas más deliciosas.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($videos as $video)
                    <div class="aspect-w-16 aspect-h-9">
                        @if($video->tipo === 'youtube')
                            <iframe class="rounded-xl w-full h-full" src="{{ $video->url }}" frameborder="0" allowfullscreen></iframe>
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

   {{-- Sección: Promociones destacadas --}}
<section class="mb-16">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Promociones del Mes</h2>
    @if ($promociones->isEmpty())
        <p class="text-gray-500">No hay promociones activas en este momento.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($promociones as $promo)
                <div class="p-6 bg-red-50 rounded-xl shadow hover:shadow-md">
                    <h3 class="text-xl font-bold text-red-700 mb-2">{{ $promo->titulo }}</h3>
                    <p class="text-gray-700 mb-2">{{ $promo->descripcion }}</p>
                    @if ($promo->imagen)
                        <img src="{{ asset('storage/promociones/' . $promo->imagen) }}" alt="{{ $promo->titulo }}" class="mt-4 rounded-xl shadow w-full h-40 object-cover">
                    @endif
                    @if ($promo->enlace)
                        <a href="{{ $promo->enlace }}" target="_blank" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Ver más</a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>


    {{-- Sección: Tipos de cortes --}}
<section class="mb-16">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tipos de Cortes</h2>

    @if($cortes->isEmpty())
        <p class="text-gray-500">No hay cortes disponibles en este momento.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
            @foreach($cortes as $corte)
                <div class="text-center">
                    <img src="{{ asset('storage/cortes/' . $corte->imagen) }}" alt="{{ $corte->nombre }}" class="rounded-xl shadow-md w-full h-40 object-cover">
                    <p class="mt-2 font-semibold text-gray-700">{{ $corte->nombre }}</p>
                </div>
            @endforeach
        </div>
    @endif
</section>

</div>
@endsection

@section('footer')
    <footer class="bg-gray-100 text-gray-700 p-8 mt-10">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-xl font-bold mb-2">Sobre BEEF FRESH</h3>
                <p class="text-sm leading-relaxed">
                    Nos especializamos en la distribución de carnes de alta calidad, garantizando frescura y trazabilidad en cada corte. Somos una empresa comprometida con la salud, el sabor y la satisfacción de nuestros clientes.
                </p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-2">Nuestra Promesa</h3>
                <p class="text-sm leading-relaxed">
                    Compra 100% en línea, entregas puntuales, asesoría personalizada y un equipo experto en carnes que asegura calidad y confianza en cada pedido.
                </p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-2">Síguenos</h3>
                <div class="flex space-x-4 mt-2">
                    <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-pink-600 hover:text-pink-800"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-blue-400 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-red-600 hover:text-red-800"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
        </div>
    </footer>
@endsection
