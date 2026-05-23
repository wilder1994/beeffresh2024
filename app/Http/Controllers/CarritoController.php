<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Catalog\CartSessionService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function __construct(
        private readonly CartSessionService $cartSession,
    ) {}

    public function agregar(Request $request)
    {
        $id = (int) ($request->input('product_id') ?? $request->input('producto_id'));
        $saleUnit = $this->cartSession->parseSaleUnit($request->input('sale_unit'));
        $cantidad = $this->cartSession->normalizeQuantity($request->input('cantidad', 1));
        $product = Product::query()->findOrFail($id);

        $carrito = session()->get('carrito', []);
        $lineKey = $this->cartSession->lineKey($id, $saleUnit);
        $unitPrice = $this->cartSession->unitPrice($product, $saleUnit);

        $existingQty = isset($carrito[$lineKey]['cantidad'])
            ? (float) $carrito[$lineKey]['cantidad']
            : 0.0;
        $totalStockNeeded = $this->cartSession->stockRequired(
            $product,
            $existingQty + $cantidad,
            $saleUnit
        );

        if ((float) $product->stock < $totalStockNeeded) {
            if ($request->isJson()) {
                return response()->json([
                    'mensaje' => 'Stock insuficiente para esta cantidad.',
                    'totalProductos' => (int) round($this->cartSession->totalItemCount($carrito)),
                ], 400);
            }

            return redirect()->back()->with('error', 'Stock insuficiente para esta cantidad.');
        }

        if (isset($carrito[$lineKey])) {
            $carrito[$lineKey]['cantidad'] = $existingQty + $cantidad;
        } else {
            $carrito[$lineKey] = [
                'product_id' => $id,
                'sale_unit' => $saleUnit->value,
                'nombre' => $product->name,
                'precio' => $unitPrice,
                'imagen' => $product->image,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        $totalProductos = (int) round($this->cartSession->totalItemCount($carrito));
        $unitLabel = $saleUnit->value === 'lb' ? 'lb' : 'kg';

        if ($request->isJson()) {
            return response()->json([
                'mensaje' => "Agregado: {$cantidad} {$unitLabel} de {$product->name}",
                'totalProductos' => $totalProductos,
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito.');
    }

    public function ver()
    {
        $carritoSession = session()->get('carrito', []);
        $productIds = $this->cartSession->productIds($carritoSession);

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $lineas = [];
        $total = 0;
        $itemCount = 0;

        foreach ($carritoSession as $lineKey => $item) {
            if (! is_array($item) || ! isset($item['cantidad'])) {
                continue;
            }

            [$productId, $saleUnit] = $this->cartSession->parseLineKey($lineKey);
            $product = $products->get($productId);

            if ($product === null) {
                continue;
            }

            $cantidad = (float) $item['cantidad'];
            $precio = $this->cartSession->unitPrice($product, $saleUnit);
            $subtotal = $precio * $cantidad;

            $lineas[] = [
                'product_id' => $product->id,
                'nombre' => $product->name,
                'precio' => $precio,
                'cantidad' => $cantidad,
                'sale_unit' => $saleUnit,
                'subtotal' => $subtotal,
                'imagen_url' => $product->imageUrl(),
            ];

            $total += $subtotal;
            $itemCount += $cantidad;
        }

        return view('carrito.ver', compact('lineas', 'total', 'itemCount'));
    }

    public function finalizarCompra(CheckoutService $checkoutService): RedirectResponse
    {
        $user = auth()->user();
        $carrito = session()->get('carrito', []);

        if ($carrito === []) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        if (! $user->isCustomer()) {
            return redirect()->back()->with('error', 'Solo las cuentas de cliente pueden finalizar compras en línea.');
        }

        if (! $user->hasCompleteDeliveryProfile()) {
            return redirect()
                ->back()
                ->with('error', 'Completa en tu perfil el teléfono y la dirección de entrega (ciudad y provincia) antes de pedir a domicilio.')
                ->with('open_profile_modal', true);
        }

        try {
            $order = $checkoutService->finalizeCart($user, $carrito);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        session()->forget('carrito');

        return redirect()
            ->route('home')
            ->with('success', 'Pedido #'.$order->id.' registrado correctamente.');
    }
}
