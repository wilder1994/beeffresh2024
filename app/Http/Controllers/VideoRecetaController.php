<?php

namespace App\Http\Controllers;

use App\Models\VideoReceta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoRecetaController extends Controller
{
    public function index()
    {
        $videos = VideoReceta::all();
        return view('videos.index', compact('videos'));
    }

    public function create()
    {
        return view('videos.create');
    }

   public function store(Request $request)
{
    $request->validate([
        'titulo' => 'required|string|max:255',
        'tipo' => 'required|in:youtube,archivo',
        'url' => 'nullable|url',
        'archivo' => 'nullable|file|mimes:mp4,webm,ogg|max:100000',
    ]);

    $data = [
        'titulo' => $request->titulo,
        'tipo' => $request->tipo,
    ];

    // Si el tipo es YouTube, transformamos el enlace en formato embed
    if ($request->tipo === 'youtube') {
        $youtubeUrl = $request->url;

        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $youtubeUrl, $matches);
        $videoId = $matches[1] ?? null;

        if ($videoId) {
            $data['url'] = 'https://www.youtube.com/embed/' . $videoId;
        } else {
            return back()->withErrors(['url' => 'La URL de YouTube no es válida.'])->withInput();
        }
    }

    // Si es un archivo, lo subimos
    if ($request->hasFile('archivo')) {
        $ruta = $request->file('archivo')->store('videos', 'public');
        $data['archivo'] = basename($ruta);
    }

    VideoReceta::create($data);

    return redirect()->route('videos.index')->with('success', 'Video guardado correctamente.');
}

    public function edit(VideoReceta $video)
{
    return view('videos.edit', compact('video'));
}
    public function update(Request $request, VideoReceta $video)
{
    $request->validate([
        'titulo' => 'required|string|max:255',
        'tipo' => 'required|in:youtube,archivo',
        'url' => 'nullable|url',
        'archivo' => 'nullable|file|mimes:mp4,webm,ogg|max:100000',
    ]);

    $video->titulo = $request->titulo;
    $video->tipo = $request->tipo;

    if ($request->tipo === 'youtube') {
        $youtubeUrl = $request->url;
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $youtubeUrl, $matches);
        $videoId = $matches[1] ?? null;

        if ($videoId) {
            $video->url = 'https://www.youtube.com/embed/' . $videoId;

            // Si había un archivo anterior, eliminarlo
            if ($video->archivo) {
                Storage::disk('public')->delete('videos/' . $video->archivo);
                $video->archivo = null;
            }
        } else {
            return back()->withErrors(['url' => 'La URL de YouTube no es válida.'])->withInput();
        }
    }

    if ($request->tipo === 'archivo' && $request->hasFile('archivo')) {
        // Eliminar archivo anterior si existe
        if ($video->archivo) {
            Storage::disk('public')->delete('videos/' . $video->archivo);
        }

        $ruta = $request->file('archivo')->store('videos', 'public');
        $video->archivo = basename($ruta);
        $video->url = null; // limpiar enlace si antes era YouTube
    }

    $video->save();

    return redirect()->route('videos.index')->with('success', 'Video actualizado correctamente.');
}

    public function destroy(VideoReceta $video)
{
    if ($video->archivo) {
        Storage::disk('public')->delete('videos/' . $video->archivo);
    }

    $video->delete();

    return redirect()->route('videos.index')->with('success', 'Video eliminado.');
}
}
