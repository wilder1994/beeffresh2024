@extends('layouts.store')

@section('titulo', 'Inicio | BEEF FRESH · Tienda')

@section('content')
    <x-store.cinta-carousel :tiles="$cintaTiles" />

    @if($promoProducts->isNotEmpty())
        <x-store.home-section tone="cream">
            <x-store.home-section-head
                title="Promociones del mes"
                :link-url="route('products.public.index', ['promo' => 1])"
                link-label="Ver catálogo →"
            />
            <div class="bf-home-products__grid">
                @foreach($promoProducts as $row)
                    @php $product = $row['product']; @endphp
                    <x-store.home-product-card
                        :url="route('products.public.show', $product)"
                        :product-id="$product->id"
                        :image-url="$product->imageUrl()"
                        :title="$product->name"
                        badge="Promo"
                        :price-label="$row['unit_price']"
                        :reference-price="$row['reference_price']"
                        :meta="trim(($product->meatType?->name ?? '').' · '.($product->meatCut?->name ?? ''))"
                    />
                @endforeach
            </div>
        </x-store.home-section>
    @endif

    @if($offers->isNotEmpty())
        <x-store.home-section tone="white">
            <x-store.home-section-head
                title="Combos y packs"
            />
            <div class="bf-home-products__grid">
                @foreach($offers as $row)
                    <x-store.home-offer-card
                        :offer="$row['offer']"
                        :reference-price="$row['reference_price']"
                        :offer-price="$row['offer_price']"
                        :unit-suffix="$row['unit_suffix']"
                        :volume-summary="$row['volume_summary']"
                        :available="$row['available']"
                        :label="$row['label']"
                    />
                @endforeach
            </div>
        </x-store.home-section>
    @endif

    @if($featuredProducts->isNotEmpty())
        <x-store.home-section tone="cream">
            <x-store.home-section-head
                title="Los más pedidos"
                :link-url="route('products.public.index')"
                link-label="Ver catálogo →"
            />
            <div class="bf-home-products__grid">
                @foreach($featuredProducts as $row)
                    @php $product = $row['product']; @endphp
                    <x-store.home-product-card
                        :url="route('products.public.show', $product)"
                        :product-id="$product->id"
                        :image-url="$product->imageUrl()"
                        :title="$product->name"
                        badge="Destacado"
                        :price-label="$row['unit_price']"
                        :meta="trim(($product->meatType?->name ?? '').' · '.($product->meatCut?->name ?? ''))"
                    />
                @endforeach
            </div>
        </x-store.home-section>
    @endif

    @if($meatCuts->isNotEmpty())
        <x-store.home-section tone="white">
            <x-store.home-section-head
                title="Tipos de corte"
                :link-url="route('products.public.index')"
                link-label="Ver todo el catálogo →"
            />
            <div class="bf-home-cuts__grid">
                @foreach($meatCuts as $item)
                    @php $cut = $item['cut']; @endphp
                    <x-store.home-cut-card
                        :name="$cut->name"
                        :meta="$cut->meatType?->name"
                        :image-url="$item['image_url']"
                        :products-count="$item['products_count']"
                        :catalog-url="$item['catalog_url']"
                    />
                @endforeach
            </div>
        </x-store.home-section>
    @endif

    @if($videos->isNotEmpty())
        <x-store.home-section tone="cream">
            <x-store.home-section-head
                eyebrow="Inspiración"
                title="Recetas en video"
                subtitle="Ideas para cocinar nuestras carnes."
            />
            @php $videoCount = $videos->count(); @endphp
            <div class="bf-home-videos__grid bf-home-videos__grid--{{ $videoCount }}">
                @foreach($videos as $index => $item)
                    @php $video = $item['video']; @endphp
                    <x-store.home-video-card
                        :title="$video->titulo"
                        :featured="$index === 0"
                        :is-youtube="$item['is_youtube']"
                        :embed-url="$item['embed_url']"
                        :thumbnail-url="$item['thumbnail_url']"
                        :file-url="$item['file_url']"
                    />
                @endforeach
            </div>
        </x-store.home-section>
    @endif

    <section class="bf-home-cta" aria-label="Conoce la empresa">
        <div class="bf-home-cta__inner">
            <div class="bf-home-cta__panel">
                <h2 class="bf-home-cta__title">Calidad que se siente en cada corte</h2>
                <p class="bf-home-cta__text">Conoce nuestra historia, compromiso y canales de contacto.</p>
                <a href="{{ route('nosotros') }}" class="bf-btn-primary inline-flex justify-center">Conoce más sobre nosotros</a>
            </div>
        </div>
    </section>
@endsection
