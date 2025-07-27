<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class CarritoController extends Controller
{
        public function agregar(Request $request)
    {
        $id = (int) $request->input('producto_id'); // <-- ID desde JSON

        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $producto = Producto::findOrFail($id);
        if ($producto->stock < $cantidad) {
            return response()->json([
                'mensaje' => 'Stock insuficiente para este producto.',
                'totalProductos' => array_sum(array_column($carrito, 'cantidad')),
            ], 400);
        }


        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] += $cantidad;
        } else {
            $carrito[$id] = [
                'producto_id' => $id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'imagen' => $producto->imagen,
                'cantidad' => $cantidad
            ];
        }

        session()->put('carrito', $carrito);

        return response()->json([
            'mensaje' => 'Producto agregado al carrito',
            'totalProductos' => array_sum(array_column($carrito, 'cantidad')),
        ]);
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

        public function finalizarCompra()
    {
        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        foreach ($carrito as $item) {
           $producto = Producto::find($item['producto_id']);

            if (!$producto) {
                continue; // salta al siguiente
            }

            if ($producto) {
                // Validar que hay suficiente stock
                if ($producto->stock >= $item['cantidad']) {
                    $producto->stock -= $item['cantidad'];
                    $producto->save();
                } else {
                    return redirect()->back()->with('error', "Stock insuficiente para el producto: {$producto->nombre}");
                }
            }
        }

        // Vaciar carrito
        session()->forget('carrito');

        return redirect()->route('productos.index')->with('success', 'Compra realizada con éxito');
    }

}
