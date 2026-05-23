<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Catalog\ProductPriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with(['meatType', 'meatCut'])
            ->orderBy('name')
            ->paginate(30);

        return view('catalog.pricing.index', compact('products'));
    }

    public function update(Request $request, ProductPriceCalculator $calculator): RedirectResponse
    {
        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.id' => ['required', 'integer', 'exists:products,id'],
            'prices.*.price_per_kg' => ['required', 'numeric', 'min:0'],
            'prices.*.price_per_lb' => ['nullable', 'numeric', 'min:0'],
            'sync_lb' => ['sometimes', 'boolean'],
        ]);

        $syncLb = $request->boolean('sync_lb');

        foreach ($validated['prices'] as $row) {
            $product = Product::query()->findOrFail((int) $row['id']);
            $priceKg = (float) $row['price_per_kg'];
            $priceLb = isset($row['price_per_lb']) && $row['price_per_lb'] !== ''
                ? (float) $row['price_per_lb']
                : ($syncLb ? $calculator->lbFromKg($priceKg) : (float) $product->price_per_lb);

            $product->update([
                'price_per_kg' => $priceKg,
                'price_per_lb' => $priceLb,
            ]);
        }

        return redirect()
            ->route('catalog.pricing.index')
            ->with('success', 'Precios actualizados.');
    }
}
