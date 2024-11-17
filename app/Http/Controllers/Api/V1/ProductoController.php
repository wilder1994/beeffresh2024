<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Producto::all(), 200); //mostrar todos los productos
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validar datos
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripciòn' =>['nullable', 'string', 'max:255'],
            'precio' =>['required', 'integer', 'min:10000'],
            'stock' =>['required', 'integer', 'min:1'],
        ]);
        // guardar datos
        $producto = Producto::create($datos);

        //respuesta al cliente
        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        return response()->json($producto, 200); //mostrar producto
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripciòn' =>['nullable', 'string', 'max:255'],
            'precio' =>['required', 'integer', 'min:10000'],
            'stock' =>['required', 'integer', 'min:1'],
        ]);
        // atualizar datos
        $producto->update($datos);

        //respuesta al cliente
        return response()->json([
            'success' => true,
            'message' => 'Producto atualizdao exitosamente',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        //eliminar producto
        $producto->delete();

         //respuesta al cliente
         return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente',
        ], 204);
    }
}
