<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Domain\Catalog\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Product;
use App\Services\Catalog\ProductPromotionResolver;
use App\Services\Store\StoreCatalogCardPresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPublicController extends Controller
{
    public function __construct(
        private readonly StoreCatalogCardPresenter $catalogCards,
        private readonly ProductPromotionResolver $promotionResolver,
    ) {}

    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['meatType', 'meatCut'])
            ->where('status', ProductStatus::Available);

        if ($request->boolean('promo')) {
            $query->where(function ($builder): void {
                $builder->whereNotNull('promo_price_kg')
                    ->orWhereNotNull('promo_price_lb');
            });
        }

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

        $products = $query->orderByDesc('featured')->orderBy('name')->get()
            ->filter(function (Product $product) use ($request): bool {
                if (! $request->boolean('promo')) {
                    return true;
                }

                return $this->promotionResolver->isActive($product);
            })
            ->values();

        $catalogRows = $products->map(fn (Product $product): array => [
            'product' => $product,
            'card' => $this->catalogCards->forProduct($product),
        ]);

        $meatTypes = MeatType::query()->orderBy('name')->get();
        $selectedMeatCut = $request->filled('meat_cut_id')
            ? MeatCut::query()->with('meatType')->find((int) $request->query('meat_cut_id'))
            : null;

        return view('public.products.index', [
            'catalogRows' => $catalogRows,
            'meatTypes' => $meatTypes,
            'selectedMeatCut' => $selectedMeatCut,
            'promoFilter' => $request->boolean('promo'),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === ProductStatus::Available, 404);

        $product->load(['meatType', 'meatCut']);

        return view('public.products.show', compact('product'));
    }
}
