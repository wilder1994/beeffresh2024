<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoPublicoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%'.$request->buscar.'%');

            if ($request->filled('categoria')) {
                $query->where('categoria_id', $request->categoria);
            }
        }

        $productos = $query->get();
        $categorias = Categoria::all();

        return view('public.productos.index', compact('productos', 'categorias'));
    }

    public function show(Producto $producto)
    {
        return view('public.productos.show', compact('producto'));
    }
}
