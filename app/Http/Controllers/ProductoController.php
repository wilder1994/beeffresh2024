<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Mostrar todos los productos.
     */
    public function index()
    {
        $productos = Producto::all();
        return view('productos.index', compact('productos'));
    }

    /**
     * Mostrar el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return view('productos.create');
    }

    /**
     * Almacenar un producto recién creado.
     */
    public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string',
        'descripcion' => 'nullable|string',
        'precio' => 'required|numeric',
        'unidad' => 'required|in:kilo,libra',
        'promocion' => 'nullable|string|max:255',
        'stock' => 'required|integer',
        'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);


    // Verificar y guardar imagen
    if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
        $imagenPath = $request->file('imagen')->store('imagenes', 'public');
        $imagenNombre = basename($imagenPath);
    } else {
        return back()->withErrors(['imagen' => 'Error al subir la imagen'])->withInput();
    }

    Producto::create([
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion,
        'precio' => $request->precio,
        'unidad' => $request->unidad,
        'promocion' => $request->promocion,
        'stock' => $request->stock,
        'imagen' => $imagenNombre,
    ]);


    return redirect()->route('productos.index')->with('success', 'Producto creado con éxito.');
}


    /**
     * Mostrar el formulario para editar un producto.
     */
    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    /**
     * Actualizar un producto existente.
     */
    public function update(Request $request, Producto $producto)
    {
        // Validación
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'unidad' => 'required|in:kilo,libra',
            'promocion' => 'nullable|string|max:255',
            'stock' => 'required|integer',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        // Reemplazar imagen si se sube una nueva
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && Storage::disk('public')->exists('imagenes/' . $producto->imagen)) {
                Storage::disk('public')->delete('imagenes/' . $producto->imagen);
            }

            // Guardar nueva imagen
            $imagenPath = $request->file('imagen')->store('imagenes', 'public');
            $producto->imagen = basename($imagenPath);
        }

        // Actualizar los demás campos
       $producto->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'unidad' => $request->unidad,
            'promocion' => $request->promocion,
            'stock' => $request->stock,
            'imagen' => $producto->imagen, // si se cambió, ya fue actualizado arriba
        ]);


        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente');
    }

    /**
     * Eliminar un producto.
     */
    public function destroy(Producto $producto)
    {
        // Eliminar imagen si existe
        if ($producto->imagen && Storage::disk('public')->exists('imagenes/' . $producto->imagen)) {
            Storage::disk('public')->delete('imagenes/' . $producto->imagen);
        }

        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente');
    }

    /**
     * Mostrar un producto específico (no implementado).
     */
    public function show(Producto $producto)
    {
        // Puedes implementar esta vista si deseas mostrar detalles individuales.
    }
}
