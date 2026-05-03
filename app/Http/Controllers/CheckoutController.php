<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    /**
     * Resumen antes del pago (pasarela pendiente). Solo usuarios autenticados.
     */
    public function show(): View|RedirectResponse
    {
        $carritoSession = session()->get('carrito', []);

        if ($carritoSession === []) {
            return redirect()
                ->route('carrito.ver')
                ->with('error', 'Tu carrito está vacío.');
        }

        $productos = Producto::whereIn('id', array_keys($carritoSession))->get();

        $lineas = [];
        $total = 0;

        foreach ($productos as $producto) {
            $item = $carritoSession[$producto->id] ?? null;
            if (! is_array($item) || ! isset($item['cantidad'])) {
                continue;
            }
            $subtotal = (float) $producto->precio * (int) $item['cantidad'];
            $total += $subtotal;
            $lineas[] = [
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'cantidad' => $item['cantidad'],
                'subtotal' => $subtotal,
                'imagen' => $producto->imagen,
            ];
        }

        return view('checkout.show', compact('lineas', 'total'));
    }
}
