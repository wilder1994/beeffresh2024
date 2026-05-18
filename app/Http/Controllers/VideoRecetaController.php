<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\VideoReceta;
use App\Support\YoutubeEmbedUrl;
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
            'url' => 'required_if:tipo,youtube|nullable|url',
            'archivo' => 'required_if:tipo,archivo|nullable|file|mimes:mp4,webm,ogg|max:100000',
        ]);

        $data = [
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
        ];

        if ($request->tipo === 'youtube') {
            $embed = YoutubeEmbedUrl::resolve($request->string('url')->toString());
            if ($embed === null) {
                return back()->withErrors([
                    'url' => 'No se reconoció el enlace de YouTube. Usa un enlace de watch (…?v=…), youtu.be, Shorts o embed.',
                ])->withInput();
            }
            $data['url'] = $embed;
        }

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
            'url' => 'required_if:tipo,youtube|nullable|url',
            'archivo' => 'nullable|file|mimes:mp4,webm,ogg|max:100000',
        ]);

        $video->titulo = $request->titulo;
        $video->tipo = $request->tipo;

        if ($request->tipo === 'youtube') {
            $embed = YoutubeEmbedUrl::resolve($request->string('url')->toString());
            if ($embed === null) {
                return back()->withErrors([
                    'url' => 'No se reconoció el enlace de YouTube. Usa un enlace de watch (…?v=…), youtu.be, Shorts o embed.',
                ])->withInput();
            }
            $video->url = $embed;

            if ($video->archivo) {
                Storage::disk('public')->delete('videos/'.$video->archivo);
                $video->archivo = null;
            }
        }

        if ($request->tipo === 'archivo' && $request->hasFile('archivo')) {
            if ($video->archivo) {
                Storage::disk('public')->delete('videos/'.$video->archivo);
            }

            $ruta = $request->file('archivo')->store('videos', 'public');
            $video->archivo = basename($ruta);
            $video->url = null;
        }

        $video->save();

        return redirect()->route('videos.index')->with('success', 'Video actualizado correctamente.');
    }

    public function destroy(VideoReceta $video)
    {
        if ($video->archivo) {
            Storage::disk('public')->delete('videos/'.$video->archivo);
        }

        $video->delete();

        return redirect()->route('videos.index')->with('success', 'Video eliminado.');
    }
}
