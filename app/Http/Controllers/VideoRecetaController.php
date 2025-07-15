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

        $data = $request->only(['titulo', 'tipo', 'url']);

        if ($request->hasFile('archivo')) {
            $ruta = $request->file('archivo')->store('videos', 'public');
            $data['archivo'] = basename($ruta);
        }

        VideoReceta::create($data);

        return redirect()->route('videos.index')->with('success', 'Video guardado correctamente.');
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
