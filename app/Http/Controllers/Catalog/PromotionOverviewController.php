<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class PromotionOverviewController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with(['meatType', 'meatCut'])
            ->whereNotNull('promo_price_kg')
            ->orderByDesc('promo_end')
            ->paginate(24);

        return view('catalog.promotions.index', compact('products'));
    }
}
