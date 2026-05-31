<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartSessionService;
use App\Services\Catalog\CartStorage;
use App\Services\Catalog\CartViewService;
use App\Services\Store\OfferAvailabilityService;
use App\Support\Realtime\ProductStockPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly CartStorage $cartStorage,
        private readonly CartViewService $cartView,
        private readonly OfferAvailabilityService $offerAvailability,
    ) {}

    public function agregar(Request $request)
    {
        $id = (int) ($request->input('product_id') ?? $request->input('producto_id'));
        $saleUnit = $this->cartSession->parseSaleUnit($request->input('sale_unit'));
        $cantidad = $this->cartSession->normalizeQuantity($request->input('cantidad', 1));
        $product = Product::query()->findOrFail($id);

        $carrito = $this->cartStorage->get();
        $lineKey = $this->cartSession->productLineKey($id, $saleUnit);

        $existingQty = isset($carrito[$lineKey]['cantidad'])
            ? (float) $carrito[$lineKey]['cantidad']
            : 0.0;
        $newQty = $existingQty + $cantidad;
        $totalStockNeeded = $this->cartSession->stockRequired($product, $newQty, $saleUnit);

        if ((float) $product->stock < $totalStockNeeded) {
            return $this->cartErrorResponse($request, $carrito, $this->stockLimitMessage($product, $saleUnit));
        }

        $unitPrice = $this->cartSession->unitPrice($product, $saleUnit, $newQty);
        $quote = $this->cartSession->priceQuote($product, $saleUnit, $newQty);

        $carrito[$lineKey] = [
            'type' => 'product',
            'product_id' => $id,
            'sale_unit' => $saleUnit->value,
            'nombre' => $product->name,
            'precio' => $unitPrice,
            'imagen' => $product->image,
            'cantidad' => $newQty,
            'pricing_tier' => $quote->tier,
            'pricing_label' => $quote->pricingLabel(),
        ];

        $this->cartStorage->put($carrito);

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
            return $this->cartErrorResponse($request, $this->cartStorage->get(), 'Esta oferta no se puede agregar como pack.');
        }

        $carrito = $this->cartStorage->get();
        $lineKey = $this->cartSession->offerLineKey($offerId);

        $existingQty = isset($carrito[$lineKey]['cantidad'])
            ? (int) $carrito[$lineKey]['cantidad']
            : 0;
        $newQty = $existingQty + $cantidad;

        if ($this->offerAvailability->availableUnits($offer) < $newQty) {
            return $this->cartErrorResponse($request, $carrito, 'No hay suficientes packs disponibles.');
        }

        $carrito[$lineKey] = [
            'type' => 'offer',
            'offer_id' => $offerId,
            'nombre' => $offer->name,
            'precio' => (float) $offer->offer_price,
            'imagen' => $offer->image,
            'cantidad' => $newQty,
            'sale_unit' => StockUnit::Pack->value,
        ];

        $this->cartStorage->put($carrito);

        if ($request->isJson()) {
            return response()->json([
                'mensaje' => "Agregado: {$cantidad} × {$offer->name}",
                'totalProductos' => (int) round($this->cartSession->totalItemCount($carrito)),
            ]);
        }

        return redirect()->back()->with('success', 'Pack agregado al carrito.');
    }

    public function ver(): View
    {
        $summary = $this->cartView->summarize($this->cartStorage->get());

        return view('carrito.ver', [
            'lineas' => $summary['lineas'],
            'total' => $summary['total'],
            'itemCount' => $summary['itemCount'],
        ]);
    }

    public function validar(): JsonResponse
    {
        $summary = $this->cartView->summarize($this->cartStorage->get());
        $lines = [];

        foreach ($summary['lineas'] as $linea) {
            if (($linea['tipo'] ?? '') === 'product' && isset($linea['product_id'])) {
                $product = Product::query()->find((int) $linea['product_id']);

                if ($product === null) {
                    $lines[] = [
                        'product_id' => (int) $linea['product_id'],
                        'line_key' => $linea['line_key'],
                        'can_purchase' => false,
                        'availability_label' => 'No disponible',
                    ];

                    continue;
                }

                $lines[] = [
                    'product_id' => $product->id,
                    'line_key' => $linea['line_key'],
                    'can_purchase' => $product->isPurchasable(),
                    'availability_label' => ProductStockPayload::availabilityLabel($product),
                ];

                continue;
            }

            if (($linea['tipo'] ?? '') === 'offer') {
                $offerId = $this->cartSession->parseOfferLineKey((string) $linea['line_key']);
                $offer = Offer::query()->with(['items.product'])->find($offerId);
                $available = $offer !== null
                    && $this->offerAvailability->availableUnits($offer) >= (int) $linea['cantidad'];

                $lines[] = [
                    'line_key' => $linea['line_key'],
                    'offer_id' => $offerId,
                    'can_purchase' => $available,
                    'availability_label' => $available ? 'Disponible' : 'Agotado',
                ];
            }
        }

        $hasInvalid = collect($lines)->contains(fn (array $row): bool => ! ($row['can_purchase'] ?? false));

        return response()->json([
            'lines' => $lines,
            'has_invalid' => $hasInvalid,
            'checkout_allowed' => ! $hasInvalid && $lines !== [],
        ]);
    }

    public function actualizarLinea(Request $request): RedirectResponse
    {
        $lineKey = (string) $request->input('line_key', '');
        $carrito = $this->cartStorage->get();

        if ($lineKey === '' || ! isset($carrito[$lineKey])) {
            return redirect()->route('carrito.ver')->with('error', 'La línea del carrito ya no existe.');
        }

        if ($this->cartSession->isOfferLine($lineKey)) {
            return $this->updateOfferLine($request, $carrito, $lineKey);
        }

        return $this->updateProductLine($request, $carrito, $lineKey);
    }

    public function eliminarLinea(Request $request): RedirectResponse
    {
        $lineKey = (string) $request->input('line_key', '');
        $carrito = $this->cartStorage->get();

        if ($lineKey === '' || ! isset($carrito[$lineKey])) {
            return redirect()->route('carrito.ver')->with('error', 'La línea del carrito ya no existe.');
        }

        unset($carrito[$lineKey]);
        $this->cartStorage->put($carrito);

        return redirect()->route('carrito.ver')->with('success', 'Producto eliminado del carrito.');
    }

    public function finalizarCompra(): RedirectResponse
    {
        return redirect()
            ->route('checkout.show')
            ->with('error', 'Usa el checkout seguro para completar tu pago en línea.');
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $carrito
     */
    private function updateProductLine(Request $request, array $carrito, string $lineKey): RedirectResponse
    {
        [$productId, $saleUnit] = $this->cartSession->parseProductLineKey($lineKey);
        $product = Product::query()->find($productId);

        if ($product === null) {
            unset($carrito[$lineKey]);
            $this->cartStorage->put($carrito);

            return redirect()->route('carrito.ver')->with('error', 'El producto ya no está disponible.');
        }

        $cantidad = $this->cartSession->normalizeQuantity($request->input('cantidad', 1));
        $stockNeeded = $this->cartSession->stockRequired($product, $cantidad, $saleUnit);

        if ((float) $product->stock < $stockNeeded) {
            return redirect()->route('carrito.ver')->with('error', $this->stockLimitMessage($product, $saleUnit));
        }

        $quote = $this->cartSession->priceQuote($product, $saleUnit, $cantidad);

        $carrito[$lineKey]['cantidad'] = $cantidad;
        $carrito[$lineKey]['precio'] = $quote->unitPrice;
        $carrito[$lineKey]['pricing_tier'] = $quote->tier;
        $carrito[$lineKey]['pricing_label'] = $quote->pricingLabel();
        $carrito[$lineKey]['nombre'] = $product->name;

        $this->cartStorage->put($carrito);

        return redirect()->route('carrito.ver')->with('success', 'Cantidad actualizada.');
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $carrito
     */
    private function updateOfferLine(Request $request, array $carrito, string $lineKey): RedirectResponse
    {
        $offerId = $this->cartSession->parseOfferLineKey($lineKey);
        $offer = Offer::query()->with(['items.product'])->find($offerId);

        if ($offer === null || ! $offer->isBundle()) {
            unset($carrito[$lineKey]);
            $this->cartStorage->put($carrito);

            return redirect()->route('carrito.ver')->with('error', 'El pack ya no está disponible.');
        }

        $cantidad = max(1, (int) $request->input('cantidad', 1));

        if ($this->offerAvailability->availableUnits($offer) < $cantidad) {
            return redirect()->route('carrito.ver')->with('error', 'No hay suficientes packs disponibles.');
        }

        $carrito[$lineKey]['cantidad'] = $cantidad;
        $carrito[$lineKey]['precio'] = (float) $offer->offer_price;
        $carrito[$lineKey]['nombre'] = $offer->name;

        $this->cartStorage->put($carrito);

        return redirect()->route('carrito.ver')->with('success', 'Cantidad actualizada.');
    }

    private function stockLimitMessage(Product $product, StockUnit $saleUnit): string
    {
        $maxUnits = $this->cartSession->maxPurchasableUnits($product, $saleUnit);

        if ($maxUnits <= 0) {
            return "{$product->name} está agotado.";
        }

        return "Solo quedan {$maxUnits} {$saleUnit->value} disponibles de {$product->name}.";
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
