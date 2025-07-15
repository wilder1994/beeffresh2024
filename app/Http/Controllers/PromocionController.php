<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::all();
        return view('promociones.index', compact('promociones'));
    }

    public function create()
    {
        return view('promociones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagen = null;

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen')->store('promociones', 'public');
        }

        Promocion::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $imagen ? basename($imagen) : null,
        ]);

        return redirect()->route('promociones.index')->with('success', 'Promoción creada con éxito.');
    }

    public function edit(Promocion $promocion)
    {
        return view('promociones.edit', compact('promocion'));
    }

    public function update(Request $request, Promocion $promocion)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            if ($promocion->imagen && Storage::disk('public')->exists('promociones/' . $promocion->imagen)) {
                Storage::disk('public')->delete('promociones/' . $promocion->imagen);
            }

            $imagen = $request->file('imagen')->store('promociones', 'public');
            $promocion->imagen = basename($imagen);
        }

        $promocion->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $promocion->imagen,
        ]);

        return redirect()->route('promociones.index')->with('success', 'Promoción actualizada con éxito.');
    }

    public function destroy(Promocion $promocion)
    {
        if ($promocion->imagen && Storage::disk('public')->exists('promociones/' . $promocion->imagen)) {
            Storage::disk('public')->delete('promociones/' . $promocion->imagen);
        }

        $promocion->delete();
        return redirect()->route('promociones.index')->with('success', 'Promoción eliminada.');
    }
}
