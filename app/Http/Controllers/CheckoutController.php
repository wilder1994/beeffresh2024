<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Catalog\CartSessionService;
use App\Services\Catalog\ProductPromotionResolver;
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
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        $resolver = app(ProductPromotionResolver::class);

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
            $precio = $resolver->effectivePrice($product, $saleUnit);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            $itemCount += $cantidad;

            $lineas[] = [
                'nombre' => $product->name,
                'precio' => $precio,
                'cantidad' => $cantidad,
                'sale_unit' => $saleUnit,
                'subtotal' => $subtotal,
            ];
        }

        return view('checkout.show', compact('lineas', 'total', 'itemCount'));
    }
}
