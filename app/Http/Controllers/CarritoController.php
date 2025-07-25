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

}
