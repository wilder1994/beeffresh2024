<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartSessionService;
use App\Services\CheckoutService;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly OfferAvailabilityService $offerAvailability,
        private readonly OfferPricingService $offerPricing,
    ) {}

    public function agregar(Request $request)
    {
        $id = (int) ($request->input('product_id') ?? $request->input('producto_id'));
        $saleUnit = $this->cartSession->parseSaleUnit($request->input('sale_unit'));
        $cantidad = $this->cartSession->normalizeQuantity($request->input('cantidad', 1));
        $product = Product::query()->findOrFail($id);

        $carrito = session()->get('carrito', []);
        $lineKey = $this->cartSession->productLineKey($id, $saleUnit);

        $existingQty = isset($carrito[$lineKey]['cantidad'])
            ? (float) $carrito[$lineKey]['cantidad']
            : 0.0;
        $newQty = $existingQty + $cantidad;
        $totalStockNeeded = $this->cartSession->stockRequired($product, $newQty, $saleUnit);

        if ((float) $product->stock < $totalStockNeeded) {
            return $this->cartErrorResponse($request, $carrito, 'Stock insuficiente para esta cantidad.');
        }

        $unitPrice = $this->cartSession->unitPrice($product, $saleUnit, $newQty);

        if (isset($carrito[$lineKey])) {
            $carrito[$lineKey]['cantidad'] = $newQty;
            $carrito[$lineKey]['precio'] = $unitPrice;
        } else {
            $carrito[$lineKey] = [
                'type' => 'product',
                'product_id' => $id,
                'sale_unit' => $saleUnit->value,
                'nombre' => $product->name,
                'precio' => $unitPrice,
                'imagen' => $product->image,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        $unitLabel = $saleUnit->value;

        if ($request->isJson()) {
            return response()->json([
                'mensaje' => "Agregado: {$cantidad} {$unitLabel} de {$product->name}",
                'totalProductos' => (int) round($this->cartSession->totalItemCount($carrito)),
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito.');
    }

    public function agregarOffer(Request $request)
    {
        $offerId = (int) $request->input('offer_id');
        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $offer = Offer::query()->with(['items.product'])->findOrFail($offerId);

        if ($offer->type !== OfferType::Bundle) {
            return $this->cartErrorResponse($request, session()->get('carrito', []), 'Esta oferta no se puede agregar como pack.');
        }

        $carrito = session()->get('carrito', []);
        $lineKey = $this->cartSession->offerLineKey($offerId);

        $existingQty = isset($carrito[$lineKey]['cantidad'])
            ? (int) $carrito[$lineKey]['cantidad']
            : 0;
        $newQty = $existingQty + $cantidad;

        if ($this->offerAvailability->availableUnits($offer) < $newQty) {
            return $this->cartErrorResponse($request, $carrito, 'No hay suficientes packs disponibles.');
        }

        $unitPrice = (float) $offer->offer_price;

        $carrito[$lineKey] = [
            'type' => 'offer',
            'offer_id' => $offerId,
            'nombre' => $offer->name,
            'precio' => $unitPrice,
            'imagen' => $offer->image,
            'cantidad' => $newQty,
            'sale_unit' => StockUnit::Pack->value,
        ];

        session()->put('carrito', $carrito);

        if ($request->isJson()) {
            return response()->json([
                'mensaje' => "Agregado: {$cantidad} × {$offer->name}",
                'totalProductos' => (int) round($this->cartSession->totalItemCount($carrito)),
            ]);
        }

        return redirect()->back()->with('success', 'Pack agregado al carrito.');
    }

    public function ver()
    {
        $carritoSession = session()->get('carrito', []);
        $productIds = $this->cartSession->productIds($carritoSession);
        $offerIds = $this->cartSession->offerIds($carritoSession);

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        $offers = Offer::query()->whereIn('id', $offerIds)->get()->keyBy('id');

        $lineas = [];
        $total = 0;
        $itemCount = 0;

        foreach ($carritoSession as $lineKey => $item) {
            if (! is_array($item) || ! isset($item['cantidad'])) {
                continue;
            }

            if ($this->cartSession->isOfferLine($lineKey)) {
                $offerId = $this->cartSession->parseOfferLineKey($lineKey);
                $offer = $offers->get($offerId);
                if ($offer === null) {
                    continue;
                }

                $cantidad = (float) $item['cantidad'];
                $precio = (float) $offer->offer_price;
                $subtotal = $precio * $cantidad;

                $lineas[] = [
                    'tipo' => 'offer',
                    'nombre' => $offer->name,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'sale_unit' => StockUnit::Pack,
                    'subtotal' => $subtotal,
                    'imagen_url' => $offer->imageUrl(),
                ];

                $total += $subtotal;
                $itemCount += $cantidad;

                continue;
            }

            [$productId, $saleUnit] = $this->cartSession->parseProductLineKey($lineKey);
            $product = $products->get($productId);

            if ($product === null) {
                continue;
            }

            $cantidad = (float) $item['cantidad'];
            $precio = $this->cartSession->unitPrice($product, $saleUnit, $cantidad);
            $subtotal = $precio * $cantidad;

            $lineas[] = [
                'tipo' => 'product',
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

    /**
     * @param  array<string|int, array<string, mixed>>  $carrito
     */
    private function cartErrorResponse(Request $request, array $carrito, string $message)
    {
        if ($request->isJson()) {
            return response()->json([
                'mensaje' => $message,
                'totalProductos' => (int) round($this->cartSession->totalItemCount($carrito)),
            ], 400);
        }

        return redirect()->back()->with('error', $message);
    }
}
