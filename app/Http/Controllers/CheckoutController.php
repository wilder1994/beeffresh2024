<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartSessionService;
use App\Services\Payments\CheckoutQuoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly CheckoutQuoteService $quotes,
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

        try {
            $quote = $this->quotes->build($user, $carritoSession);
        } catch (\Throwable $e) {
            return redirect()
                ->route('carrito.ver')
                ->with('error', $e->getMessage());
        }

        $lineas = array_map(static fn ($line) => [
            'nombre' => $line->name,
            'precio' => $line->unitPrice,
            'cantidad' => $line->quantity,
            'sale_unit' => \App\Domain\Catalog\StockUnit::from($line->saleUnit),
            'subtotal' => $line->subtotal,
            'pricing_tier' => $line->meta['pricing_tier'] ?? null,
            'pricing_label' => $line->meta['pricing_label'] ?? null,
        ], $quote->lines);

        return view('checkout.show', [
            'lineas' => $lineas,
            'subtotal' => $quote->subtotal,
            'shippingFee' => $quote->shippingFee,
            'discount' => $quote->discount,
            'total' => $quote->total,
            'itemCount' => count($quote->lines),
        ]);
    }
}
