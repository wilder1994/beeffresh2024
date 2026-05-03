<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
        public function agregar(Request $request)
    {
        $id = (int) $request->input('producto_id'); // <-- ID desde JSON

        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $producto = Producto::findOrFail($id);

        $carrito = session()->get('carrito', []);

        if ($producto->stock < $cantidad) {
            if ($request->isJson()) {
                return response()->json([
                    'mensaje' => 'Stock insuficiente para este producto.',
                    'totalProductos' => array_sum(array_column($carrito, 'cantidad')),
                ], 400);
            }

            return redirect()->back()->with('error', 'Stock insuficiente para este producto.');
        }

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] += $cantidad;
        } else {
            $carrito[$id] = [
                'producto_id' => $id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'imagen' => $producto->imagen,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        $totalProductos = array_sum(array_column($carrito, 'cantidad'));

        if ($request->isJson()) {
            return response()->json([
                'mensaje' => 'Producto agregado al carrito',
                'totalProductos' => $totalProductos,
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito.');
    }


        public function ver()
    {
        $carritoSession = session()->get('carrito', []);

        $productos = \App\Models\Producto::whereIn('id', array_keys($carritoSession))->get();

        $carrito = [];

            foreach ($productos as $producto) {
                $item = $carritoSession[$producto->id] ?? null;

                if (is_array($item) && isset($item['cantidad'])) {
                    $carrito[$producto->id] = [
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio,
                        'imagen' => $producto->imagen,
                        'cantidad' => $item['cantidad'],
                    ];
                }
            }



        return view('carrito.ver', compact('carrito'));
    }

    public function finalizarCompra(CheckoutService $checkoutService)
    {
        $user = auth()->user();
        $carrito = session()->get('carrito', []);

        if ($carrito === []) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
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
