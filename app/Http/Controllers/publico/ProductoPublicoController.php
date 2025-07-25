<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoPublicoController extends Controller
{
        public function index(Request $request)
    {
        $query = Producto::query();

        // Búsqueda por nombre
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');

            // Filtro por categoría (solo si hay búsqueda activa)
            if ($request->filled('categoria')) {
                $query->where('categoria_id', $request->categoria);
            }
        }

        $productos = $query->get();
        $categorias = Categoria::all();

        return view('public.productos.index', compact('productos', 'categorias'));
    }

}
