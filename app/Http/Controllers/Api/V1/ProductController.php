<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Product::query()->with(['meatType', 'meatCut'])->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meat_type_id' => ['required', 'integer', 'exists:meat_types,id'],
            'meat_cut_id' => ['required', 'integer', 'exists:meat_cuts,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'price_per_lb' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'numeric', 'min:0'],
        ]);

        $product = Product::query()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $product->fresh(['meatType', 'meatCut']),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['meatType', 'meatCut']);

        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'meat_type_id' => ['sometimes', 'integer', 'exists:meat_types,id'],
            'meat_cut_id' => ['sometimes', 'integer', 'exists:meat_cuts,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:products,slug,'.$product->id],
            'sku' => ['sometimes', 'string', 'max:64', 'unique:products,sku,'.$product->id],
            'description' => ['nullable', 'string'],
            'price_per_kg' => ['sometimes', 'numeric', 'min:0'],
            'price_per_lb' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $product->fresh(['meatType', 'meatCut']),
        ]);
    }

    public function destroy(Product $product): Response|JsonResponse
    {
        if ($product->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el producto tiene líneas de pedido asociadas.',
            ], SymfonyResponse::HTTP_CONFLICT);
        }

        $product->deleteImageFromDisk();
        $product->delete();

        return response()->noContent();
    }
}
