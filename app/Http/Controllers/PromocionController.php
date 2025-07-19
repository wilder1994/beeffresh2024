<?php

// app/Http/Controllers/PromocionController.php

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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'enlace' => 'nullable|url',
        ]);

        $imagen = $request->hasFile('imagen')
            ? $request->file('imagen')->store('promociones', 'public')
            : null;

        Promocion::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen' => $imagen ? basename($imagen) : null,
            'enlace' => $request->enlace,
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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'enlace' => 'nullable|url',
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
            'imagen' => $promocion->imagen,
            'enlace' => $request->enlace,
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
