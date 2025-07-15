<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecetaController extends Controller
{
    public function index()
    {
        $recetas = Receta::all();
        return view('recetas.index', compact('recetas'));
    }

    public function create()
    {
        return view('recetas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:youtube,archivo',
            'url' => 'nullable|required_if:tipo,youtube|url',
            'archivo' => 'nullable|required_if:tipo,archivo|file|mimes:mp4,mov,avi|max:20480'
        ]);

        $data = [
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'url' => $request->tipo === 'youtube' ? $request->url : null,
            'archivo' => null,
        ];

        if ($request->hasFile('archivo') && $request->tipo === 'archivo') {
            $data['archivo'] = $request->file('archivo')->store('recetas', 'public');
        }

        Receta::create($data);

        return redirect()->route('recetas.index')->with('success', 'Receta guardada exitosamente.');
    }

    public function edit(Receta $receta)
    {
        return view('recetas.edit', compact('receta'));
    }

    public function update(Request $request, Receta $receta)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:youtube,archivo',
            'url' => 'nullable|required_if:tipo,youtube|url',
            'archivo' => 'nullable|file|mimes:mp4,mov,avi|max:20480'
        ]);

        $receta->titulo = $request->titulo;
        $receta->tipo = $request->tipo;
        $receta->url = $request->tipo === 'youtube' ? $request->url : null;

        if ($request->hasFile('archivo') && $request->tipo === 'archivo') {
            if ($receta->archivo && Storage::disk('public')->exists($receta->archivo)) {
                Storage::disk('public')->delete($receta->archivo);
            }
            $receta->archivo = $request->file('archivo')->store('recetas', 'public');
        }

        $receta->save();

        return redirect()->route('recetas.index')->with('success', 'Receta actualizada exitosamente.');
    }

    public function destroy(Receta $receta)
    {
        if ($receta->archivo && Storage::disk('public')->exists($receta->archivo)) {
            Storage::disk('public')->delete($receta->archivo);
        }
        $receta->delete();
        return redirect()->route('recetas.index')->with('success', 'Receta eliminada correctamente.');
    }
}

