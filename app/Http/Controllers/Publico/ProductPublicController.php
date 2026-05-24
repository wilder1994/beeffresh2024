<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Domain\Catalog\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Product;
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

    public function show(Product $product): View
    {
        abort_unless($product->status === ProductStatus::Available, 404);

        $product->load(['meatType', 'meatCut']);

        return view('public.products.show', compact('product'));
    }
}
