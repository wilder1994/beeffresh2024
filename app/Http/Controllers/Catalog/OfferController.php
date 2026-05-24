<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreOfferRequest;
use App\Http\Requests\Catalog\UpdateOfferRequest;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\OfferAdminIndexPresenter;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(
        private readonly OfferAvailabilityService $availability,
        private readonly OfferPricingService $pricing,
    ) {}

    public function bundles(OfferAdminIndexPresenter $presenter): View
    {
        $offers = Offer::query()
            ->where('type', OfferType::Bundle)
            ->with(['items.product'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rows = $offers
            ->map(fn (Offer $offer) => $presenter->bundleRow($offer))
            ->values()
            ->all();

        return view('catalog.offers.bundles.index', [
            'rows' => $rows,
            'stats' => $this->listStats($offers),
        ]);
    }

    public function volumes(OfferAdminIndexPresenter $presenter): View
    {
        $offers = Offer::query()
            ->where('type', OfferType::Volume)
            ->with(['product'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rows = $offers
            ->map(fn (Offer $offer) => $presenter->volumeRow($offer))
            ->values()
            ->all();

        return view('catalog.offers.volumes.index', [
            'rows' => $rows,
            'stats' => $this->listStats($offers),
        ]);
    }

    public function createBundle(): View
    {
        return $this->createForm(OfferType::Bundle);
    }

    public function createVolume(): View
    {
        return $this->createForm(OfferType::Volume);
    }

    public function store(StoreOfferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $type = OfferType::from($data['type']);

        $offer = Offer::query()->create([
            'type' => $type,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $this->resolveDescription($type, $data),
            'image' => basename($request->file('image')->store('offers', 'public')),
            'offer_price' => $type === OfferType::Bundle ? $data['offer_price'] : null,
            'product_id' => $type === OfferType::Volume ? $data['product_id'] : null,
            'volume_min_quantity' => $type === OfferType::Volume ? $data['volume_min_quantity'] : null,
            'volume_sale_unit' => $type === OfferType::Volume ? $data['volume_sale_unit'] : null,
            'volume_offer_price_kg' => $type === OfferType::Volume ? $data['volume_offer_price_kg'] : null,
            'volume_offer_price_lb' => $type === OfferType::Volume ? $data['volume_offer_price_lb'] : null,
            'is_active' => $request->boolean('is_active', true),
            'show_on_cinta' => $request->boolean('show_on_cinta'),
            'show_on_home' => $request->boolean('show_on_home', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        if ($type === OfferType::Bundle) {
            foreach ($data['items'] as $index => $item) {
                $offer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'sale_unit' => $item['sale_unit'],
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()
            ->route($this->listRouteFor($type))
            ->with('success', $type === OfferType::Bundle ? 'Combo creado.' : 'Escala por volumen creada.');
    }

    public function edit(Offer $offer): View
    {
        $offer->load(['items.product', 'product']);
        $products = Product::query()->with(['meatType', 'meatCut'])->orderBy('name')->get();

        return view('catalog.offers.edit', [
            'offer' => $offer,
            'products' => $products,
            'defaultType' => $offer->type,
            'cancelUrl' => route($this->listRouteFor($offer->type)),
            'pageTitle' => $offer->isBundle() ? 'Editar combo' : 'Editar escala por volumen',
        ]);
    }

    public function update(UpdateOfferRequest $request, Offer $offer): RedirectResponse
    {
        $data = $request->validated();
        $type = OfferType::from($data['type']);

        $payload = [
            'type' => $type,
            'name' => $data['name'],
            'slug' => $offer->slug,
            'description' => $this->resolveDescription($type, $data),
            'offer_price' => $type === OfferType::Bundle ? $data['offer_price'] : null,
            'product_id' => $type === OfferType::Volume ? $data['product_id'] : null,
            'volume_min_quantity' => $type === OfferType::Volume ? $data['volume_min_quantity'] : null,
            'volume_sale_unit' => $type === OfferType::Volume ? $data['volume_sale_unit'] : null,
            'volume_offer_price_kg' => $type === OfferType::Volume ? $data['volume_offer_price_kg'] : null,
            'volume_offer_price_lb' => $type === OfferType::Volume ? $data['volume_offer_price_lb'] : null,
            'is_active' => $request->boolean('is_active'),
            'show_on_cinta' => $request->boolean('show_on_cinta'),
            'show_on_home' => $request->boolean('show_on_home'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];

        if ($request->hasFile('image')) {
            $offer->deleteImageFromDisk();
            $payload['image'] = basename($request->file('image')->store('offers', 'public'));
        }

        $offer->update($payload);
        $offer->items()->delete();

        if ($type === OfferType::Bundle) {
            foreach ($data['items'] as $index => $item) {
                $offer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'sale_unit' => $item['sale_unit'],
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()
            ->route($this->listRouteFor($type))
            ->with('success', $type === OfferType::Bundle ? 'Combo actualizado.' : 'Escala por volumen actualizada.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $type = $offer->type;
        $offer->deleteImageFromDisk();
        $offer->delete();

        return redirect()
            ->route($this->listRouteFor($type))
            ->with('success', $type === OfferType::Bundle ? 'Combo eliminado.' : 'Escala por volumen eliminada.');
    }

    private function createForm(OfferType $defaultType): View
    {
        $products = Product::query()->with(['meatType', 'meatCut'])->orderBy('name')->get();

        return view('catalog.offers.create', [
            'products' => $products,
            'defaultType' => $defaultType,
            'cancelUrl' => route($this->listRouteFor($defaultType)),
            'pageTitle' => $defaultType === OfferType::Bundle ? 'Nuevo combo' : 'Nueva escala por volumen',
            'pageDescription' => $defaultType === OfferType::Bundle
                ? 'Arma un pack multi-producto con precio fijo para la tienda.'
                : 'Define cantidad mínima y precio unitario por volumen en un producto.',
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Offer>  $offers
     * @return array{total: int, active: int, inactive: int, low_stock: int}
     */
    private function listStats($offers): array
    {
        $lowStock = $offers->filter(
            fn (Offer $offer) => $this->availability->availableUnits($offer) > 0
                && $this->availability->availableUnits($offer) <= 5
        )->count();

        return [
            'total' => $offers->count(),
            'active' => $offers->where('is_active', true)->count(),
            'inactive' => $offers->where('is_active', false)->count(),
            'low_stock' => $lowStock,
        ];
    }

    private function listRouteFor(OfferType $type): string
    {
        return $type === OfferType::Bundle
            ? 'catalog.offers.bundles'
            : 'catalog.offers.volumes';
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Offer::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveDescription(OfferType $type, array $data): ?string
    {
        if ($type === OfferType::Volume) {
            $unit = StockUnit::resolve($data['volume_sale_unit'] ?? null);

            return $this->pricing->volumeSummaryText((float) ($data['volume_min_quantity'] ?? 0), $unit);
        }

        return $data['description'] ?? null;
    }
}
