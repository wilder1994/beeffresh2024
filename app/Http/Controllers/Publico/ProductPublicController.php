<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Http\Controllers\Controller;
use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPublicController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['meatType', 'meatCut'])
            ->where('status', ProductStatus::Available);

        if ($request->filled('buscar')) {
            $term = (string) $request->query('buscar');
            $query->where('name', 'like', '%'.$term.'%');
        }

        if ($request->filled('meat_type_id')) {
            $query->where('meat_type_id', (int) $request->query('meat_type_id'));
        }

        if ($request->filled('meat_cut_id')) {
            $query->where('meat_cut_id', (int) $request->query('meat_cut_id'));
        }

        $products = $query->orderBy('name')->get();
        $meatTypes = MeatType::query()->orderBy('name')->get();
        $selectedMeatCut = $request->filled('meat_cut_id')
            ? MeatCut::query()->with('meatType')->find((int) $request->query('meat_cut_id'))
            : null;

        return view('public.products.index', compact('products', 'meatTypes', 'selectedMeatCut'));
    }

    public function show(
        Product $product,
        OfferAvailabilityService $availability,
        OfferPricingService $pricing,
    ): View {
        abort_unless($product->status === ProductStatus::Available, 404);

        $product->load(['meatType', 'meatCut']);

        $volumeOfferView = null;
        $volumeOffer = Offer::query()
            ->where('type', OfferType::Volume)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($volumeOffer !== null && $availability->availableUnits($volumeOffer) > 0) {
            $unit = StockUnit::tryFrom((string) $volumeOffer->volume_sale_unit) ?? StockUnit::Kg;

            $volumeOfferView = [
                'offer' => $volumeOffer,
                'min_qty' => (float) $volumeOffer->volume_min_quantity,
                'unit' => $unit,
                'offer_unit_price' => $pricing->volumeOfferUnitPrice($volumeOffer, $unit),
                'reference_unit_price' => $pricing->referenceUnitPrice($product, $unit),
                'label' => $availability->availabilityLabel($volumeOffer),
            ];
        }

        return view('public.products.show', compact('product', 'volumeOfferView'));
    }
}
