<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Domain\Store\OfferType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreOfferRequest;
use App\Http\Requests\Catalog\UpdateOfferRequest;
use App\Models\Offer;
use App\Models\Product;
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

    public function index(): View
    {
        $offers = Offer::query()
            ->with(['product', 'items.product'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Offer $offer) => [
                'offer' => $offer,
                'reference' => $this->pricing->referenceTotal($offer),
                'offer_total' => $this->pricing->offerTotal($offer),
                'available' => $this->availability->availableUnits($offer),
            ]);

        return view('catalog.offers.index', compact('offers'));
    }

    public function create(): View
    {
        $products = Product::query()->with(['meatType', 'meatCut'])->orderBy('name')->get();

        return view('catalog.offers.create', compact('products'));
    }

    public function store(StoreOfferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $type = OfferType::from($data['type']);

        $offer = Offer::query()->create([
            'type' => $type,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
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

        return redirect()->route('catalog.offers.index')->with('success', 'Oferta creada.');
    }

    public function edit(Offer $offer): View
    {
        $offer->load(['items.product', 'product']);
        $products = Product::query()->with(['meatType', 'meatCut'])->orderBy('name')->get();

        return view('catalog.offers.edit', compact('offer', 'products'));
    }

    public function update(UpdateOfferRequest $request, Offer $offer): RedirectResponse
    {
        $data = $request->validated();
        $type = OfferType::from($data['type']);

        $payload = [
            'type' => $type,
            'name' => $data['name'],
            'slug' => $offer->slug,
            'description' => $data['description'] ?? null,
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

        return redirect()->route('catalog.offers.index')->with('success', 'Oferta actualizada.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->deleteImageFromDisk();
        $offer->delete();

        return redirect()->route('catalog.offers.index')->with('success', 'Oferta eliminada.');
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
}
