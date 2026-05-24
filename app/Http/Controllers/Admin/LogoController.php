<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LogoController extends Controller
{
    /** Logo de la empresa (tipo principal); uso desde sidebar con una sola subida. */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $logo = Logo::firstOrNew(['tipo' => 'principal']);

        if ($logo->imagen) {
            Storage::disk('public')->delete('logos/'.$logo->imagen);
        }

        $nombreImagen = time().'.'.$request->file('imagen')->extension();
        $request->file('imagen')->storeAs('logos', $nombreImagen, 'public');

        $logo->imagen = $nombreImagen;
        $logo->save();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'url' => asset('storage/logos/'.$nombreImagen)]);
        }

        return redirect()->back()->with('success', 'Logo de la empresa actualizado.');
    }
}
