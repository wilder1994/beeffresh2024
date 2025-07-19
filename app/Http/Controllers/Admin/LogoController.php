<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logo;
use Illuminate\Support\Facades\Storage;

class LogoController extends Controller
{
    /**
     * Muestra la vista con los logos actualizados.
     */
    public function index()
    {
        $logoPrincipal = Logo::where('tipo', 'principal')->first();
        $logoAdmin = Logo::where('tipo', 'administrador')->first();

        return view('admin.logos.index', compact('logoPrincipal', 'logoAdmin'));
    }

    /**
     * Muestra el formulario para editar un logo.
     */
    public function edit()
{
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoAdministrador = Logo::where('tipo', 'administrador')->first();

    return view('admin.logo.edit', compact('logoPrincipal', 'logoAdministrador'));
}


    /**
     * Actualiza o crea un logo según su tipo.
     */
   public function update(Request $request, $tipo)
{
    $request->validate([
        'imagen' => 'required|image|max:2048',
    ]);

    $logo = Logo::firstOrNew(['tipo' => $tipo]);

    if ($logo->imagen) {
        Storage::disk('public')->delete('logos/' . $logo->imagen);
    }

    $nombreImagen = time() . '.' . $request->imagen->extension();
    $request->imagen->storeAs('logos', $nombreImagen, 'public');

    $logo->imagen = $nombreImagen;
    $logo->save();

    // Diferenciar el mensaje de éxito por tipo
    $mensaje = 'Logo actualizado correctamente.';
    if ($tipo === 'principal') {
        return redirect()->route('admin.logo.edit')->with('success_principal', $mensaje);
    } elseif ($tipo === 'administrador') {
        return redirect()->route('admin.logo.edit')->with('success_administrador', $mensaje);
    }

    return redirect()->route('admin.logo.edit')->with('success', $mensaje); // fallback
}


    /**
     * Método no implementado (crear formulario de creación si es necesario).
     */
    public function create() {}

    /**
     * Método no implementado (mostrar un logo específico si es necesario).
     */
    public function show(string $id) {}

    /**
     * Método no implementado (eliminar logo si se necesita).
     */
    public function destroy(string $id) {}

    /**
     * Método para almacenar un nuevo logo (no usado si usamos solo update/edit).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:principal,administrador',
            'imagen' => 'required|image|max:2048',
        ]);

        $ruta = $request->file('imagen')->store('logos', 'public');

        Logo::updateOrCreate(
            ['tipo' => $request->tipo],
            ['imagen' => basename($ruta)]
        );

        return back()->with('success', 'Logo actualizado correctamente.');
    }
}
