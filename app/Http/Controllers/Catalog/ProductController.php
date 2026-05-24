<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Product;
use App\Services\Catalog\ProductSkuGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['meatType', 'meatCut'])
            ->withCount('orderItems')
            ->latest();

        if ($request->filled('q')) {
            $term = (string) $request->query('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%'.$term.'%')
                    ->orWhere('sku', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('meat_type_id')) {
            $query->where('meat_type_id', (int) $request->query('meat_type_id'));
        }

        $products = $query->paginate(24)->withQueryString();
        $meatTypes = MeatType::query()->orderBy('name')->get();

        return view('catalog.products.index', compact('products', 'meatTypes'));
    }

    public function create(): View
    {
        $meatTypes = MeatType::query()->orderBy('name')->get();
        $meatCuts = MeatCut::query()->with('meatType')->orderBy('name')->get();

        return view('catalog.products.create', compact('meatTypes', 'meatCuts'));
    }

    public function store(
        StoreProductRequest $request,
        ProductSkuGenerator $skuGenerator,
    ): RedirectResponse {
        $meatType = MeatType::query()->findOrFail((int) $request->input('meat_type_id'));
        $meatCut = MeatCut::query()->findOrFail((int) $request->input('meat_cut_id'));

        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) $data['name']);
        $data['sku'] = $skuGenerator->generate($meatType, $meatCut);
        $data['featured'] = $request->boolean('featured');
        $data['show_on_cinta'] = $request->boolean('show_on_cinta');

        if ($request->hasFile('image')) {
            $data['image'] = basename($request->file('image')->store('products', 'public'));
        } else {
            unset($data['image']);
        }

        Product::query()->create($data);

        return redirect()
            ->route('catalog.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $product->load(['meatType', 'meatCut']);
        $meatTypes = MeatType::query()->orderBy('name')->get();
        $meatCuts = MeatCut::query()
            ->where('meat_type_id', $product->meat_type_id)
            ->orderBy('name')
            ->get();

        return view('catalog.products.edit', compact('product', 'meatTypes', 'meatCuts'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['featured'] = $request->boolean('featured');
        $data['show_on_cinta'] = $request->boolean('show_on_cinta');

        if ($request->hasFile('image')) {
            $product->deleteImageFromDisk();
            $data['image'] = basename($request->file('image')->store('products', 'public'));
        } else {
            unset($data['image']);
        }

        if ((int) $data['meat_type_id'] !== $product->meat_type_id
            || (int) $data['meat_cut_id'] !== $product->meat_cut_id) {
            // SKU stays stable when classification changes in edit
        }

        if ($product->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug((string) $data['name'], $product->id);
        }

        $product->update($data);

        return redirect()
            ->route('catalog.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return redirect()
                ->route('catalog.products.index')
                ->with('error', 'No se puede eliminar: el producto tiene pedidos asociados.');
        }

        $product->deleteImageFromDisk();
        $product->delete();

        return redirect()
            ->route('catalog.products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function cutsByType(MeatType $meatType): JsonResponse
    {
        $cuts = $meatType->meatCuts()
            ->orderBy('name')
            ->get(['id', 'meat_type_id', 'name', 'slug']);

        return response()->json($cuts);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Product::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
