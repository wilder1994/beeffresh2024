<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ProductoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Producto::all());
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
        ]);

        $producto = Producto::query()->create($datos);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $producto,
        ], 201);
    }

    public function show(Producto $producto): JsonResponse
    {
        return response()->json($producto);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
        ]);

        $producto->update($datos);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $producto->fresh(),
        ]);
    }

    public function destroy(Producto $producto): Response|JsonResponse
    {
        if ($producto->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el producto tiene líneas de pedido asociadas.',
            ], SymfonyResponse::HTTP_CONFLICT);
        }

        $producto->delete();

        return response()->noContent();
    }
}
