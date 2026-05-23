<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['meatType', 'meatCut'])
            ->orderBy('stock');

        if ($request->boolean('low_only')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->paginate(30)->withQueryString();

        return view('catalog.inventory.index', compact('products'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock' => ['required', 'array'],
            'stock.*.id' => ['required', 'integer', 'exists:products,id'],
            'stock.*.stock' => ['required', 'numeric', 'min:0'],
            'stock.*.min_stock' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated['stock'] as $row) {
            Product::query()
                ->whereKey((int) $row['id'])
                ->update([
                    'stock' => $row['stock'],
                    'min_stock' => $row['min_stock'],
                ]);
        }

        return redirect()
            ->route('catalog.inventory.index', $request->only('low_only'))
            ->with('success', 'Inventario actualizado.');
    }
}
