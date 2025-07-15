<?php

namespace App\Http\Controllers;

use App\Models\Corte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CorteController extends Controller
{
    /**
     * Muestra todos los cortes.
     */
    public function index()
    {
        $cortes = Corte::all();
        return view('cortes.index', compact('cortes'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return view('cortes.create');
    }

    /**
     * Guarda un nuevo corte.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagen = null;

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen')->store('cortes', 'public');
        }

        Corte::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'imagen' => $imagen ? basename($imagen) : null,
        ]);

        return redirect()->route('cortes.index')->with('success', 'Corte creado exitosamente.');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(Corte $corte)
    {
        return view('cortes.edit', compact('corte'));
    }

    /**
     * Actualiza un corte.
     */
    public function update(Request $request, Corte $corte)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            // Elimina imagen anterior si existe
            if ($corte->imagen && Storage::disk('public')->exists('cortes/' . $corte->imagen)) {
                Storage::disk('public')->delete('cortes/' . $corte->imagen);
            }

            $imagen = $request->file('imagen')->store('cortes', 'public');
            $corte->imagen = basename($imagen);
        }

        $corte->nombre = $request->nombre;
        $corte->descripcion = $request->descripcion;
        $corte->save();

        return redirect()->route('cortes.index')->with('success', 'Corte actualizado exitosamente.');
    }

    /**
     * Elimina un corte.
     */
    public function destroy(Corte $corte)
    {
        if ($corte->imagen && Storage::disk('public')->exists('cortes/' . $corte->imagen)) {
            Storage::disk('public')->delete('cortes/' . $corte->imagen);
        }

        $corte->delete();

        return redirect()->route('cortes.index')->with('success', 'Corte eliminado exitosamente.');
    }
}
