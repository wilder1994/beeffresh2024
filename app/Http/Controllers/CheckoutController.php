<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartSessionService $cartSession,
    ) {}

    public function show(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isCustomer()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El checkout en línea está disponible para clientes.');
        }

        if (! $user->hasCompleteDeliveryProfile()) {
            return redirect()
                ->back()
                ->with('error', 'Antes de pagar, completa tu teléfono y dirección de entrega en Mi perfil.')
                ->with('open_profile_modal', true);
        }

        $carritoSession = session()->get('carrito', []);

        if ($carritoSession === []) {
            return redirect()
                ->route('carrito.ver')
                ->with('error', 'Tu carrito está vacío.');
        }

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

            $cantidad = (float) $item['cantidad'];

            if ($this->cartSession->isOfferLine($lineKey)) {
                $offer = $offers->get($this->cartSession->parseOfferLineKey($lineKey));
                if ($offer === null) {
                    continue;
                }

                $precio = (float) $offer->offer_price;
                $subtotal = $precio * $cantidad;
                $total += $subtotal;
                $itemCount += $cantidad;

                $lineas[] = [
                    'nombre' => $offer->name,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'sale_unit' => \App\Domain\Catalog\StockUnit::Pack,
                    'subtotal' => $subtotal,
                ];

                continue;
            }

            [$productId, $saleUnit] = $this->cartSession->parseProductLineKey($lineKey);
            $product = $products->get($productId);

            if ($product === null) {
                continue;
            }

            $precio = $this->cartSession->unitPrice($product, $saleUnit, $cantidad);
            $quote = $this->cartSession->priceQuote($product, $saleUnit, $cantidad);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            $itemCount += $cantidad;

            $lineas[] = [
                'nombre' => $product->name,
                'precio' => $precio,
                'cantidad' => $cantidad,
                'sale_unit' => $saleUnit,
                'subtotal' => $subtotal,
                'pricing_tier' => $quote->tier,
                'pricing_label' => $quote->pricingLabel(),
            ];
        }

        return view('checkout.show', compact('lineas', 'total', 'itemCount'));
    }
}
