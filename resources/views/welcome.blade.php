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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="aspect-w-16 aspect-h-9">
                    <iframe class="rounded-xl" src="https://www.youtube.com/embed/VIDEO_ID1" allowfullscreen></iframe>
                </div>
                <div class="aspect-w-16 aspect-h-9">
                    <iframe class="rounded-xl" src="https://www.youtube.com/embed/VIDEO_ID2" allowfullscreen></iframe>
                </div>
                <div class="aspect-w-16 aspect-h-9">
                    <iframe class="rounded-xl" src="https://www.youtube.com/embed/VIDEO_ID3" allowfullscreen></iframe>
                </div>
            </div>
        </section>

        {{-- Sección: Promociones destacadas --}}
        <section class="mb-16">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Promociones del Mes</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="p-6 bg-red-50 rounded-xl shadow hover:shadow-md">
                    <h3 class="text-xl font-bold text-red-700 mb-2">Combo Parrillero</h3>
                    <p class="text-gray-700">Incluye costillas, chorizo y punta de anca. ¡Ideal para el fin de semana!</p>
                </div>
                <div class="p-6 bg-red-50 rounded-xl shadow hover:shadow-md">
                    <h3 class="text-xl font-bold text-red-700 mb-2">Cortes Premium 10% OFF</h3>
                    <p class="text-gray-700">Lomo fino, bife ancho y más con descuento.</p>
                </div>
                <div class="p-6 bg-red-50 rounded-xl shadow hover:shadow-md">
                    <h3 class="text-xl font-bold text-red-700 mb-2">Pollo BBQ</h3>
                    <p class="text-gray-700">Oferta especial en pierna pernil marinada BBQ.</p>
                </div>
            </div>
        </section>

        {{-- Sección: Tipos de cortes --}}
        <section class="mb-16">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tipos de Cortes</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <img src="{{ asset('img/corte_lomo.jpg') }}" alt="Lomo fino" class="rounded-xl shadow-md w-full h-40 object-cover">
                    <p class="mt-2 font-semibold text-gray-700">Lomo fino</p>
                </div>
                <div class="text-center">
                    <img src="{{ asset('img/corte_costilla.jpg') }}" alt="Costilla" class="rounded-xl shadow-md w-full h-40 object-cover">
                    <p class="mt-2 font-semibold text-gray-700">Costilla</p>
                </div>
                <div class="text-center">
                    <img src="{{ asset('img/corte_punta.jpg') }}" alt="Punta de anca" class="rounded-xl shadow-md w-full h-40 object-cover">
                    <p class="mt-2 font-semibold text-gray-700">Punta de anca</p>
                </div>
                <div class="text-center">
                    <img src="{{ asset('img/corte_churrasco.jpg') }}" alt="Churrasco" class="rounded-xl shadow-md w-full h-40 object-cover">
                    <p class="mt-2 font-semibold text-gray-700">Churrasco</p>
                </div>
            </div>
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
