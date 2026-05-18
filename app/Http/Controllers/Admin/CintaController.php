<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CintaSlide;
use App\Rules\CintaImageAspectRatio;
use App\Support\CintaSlideStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CintaController extends Controller
{
    public function edit(): View
    {
        $slides = CintaSlide::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $maxSlides = (int) config('cinta.max_slides', 15);

        return view('admin.cinta.edit', [
            'grid' => $this->buildSlotGrid($slides, $maxSlides),
            'slideCount' => $slides->count(),
            'maxSlides' => $maxSlides,
            'spec' => config('cinta'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $maxSlides = (int) config('cinta.max_slides', 15);
        $slot = $request->integer('slot');

        $request->validate([
            'slot' => ['required', 'integer', 'min:0', 'max:'.($maxSlides - 1)],
            'imagen' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:'.(int) config('cinta.max_upload_kb', 4096),
                new CintaImageAspectRatio,
            ],
        ], [
            'imagen.required' => 'Selecciona una imagen.',
        ]);

        $existing = CintaSlide::query()->where('sort_order', $slot)->first();
        $file = $request->file('imagen');
        $newImage = CintaSlideStorage::store($file);

        if ($existing !== null) {
            CintaSlideStorage::delete($existing->image);
            $existing->update(['image' => $newImage]);

            return redirect()
                ->route('admin.cinta.edit')
                ->with('success', 'Imagen de la casilla '.($slot + 1).' actualizada.');
        }

        if (CintaSlide::query()->count() >= $maxSlides) {
            CintaSlideStorage::delete($newImage);

            return redirect()
                ->route('admin.cinta.edit')
                ->with('error', "Ya tienes el máximo de {$maxSlides} imágenes en la cinta.");
        }

        CintaSlide::query()->create([
            'image' => $newImage,
            'alt' => null,
            'link_url' => null,
            'sort_order' => $slot,
        ]);

        return redirect()
            ->route('admin.cinta.edit')
            ->with('success', 'Imagen agregada a la casilla '.($slot + 1).'.');
    }

    /**
     * @return array<int, CintaSlide|null>
     */
    private function buildSlotGrid(Collection $slides, int $maxSlides): array
    {
        $grid = array_fill(0, $maxSlides, null);

        foreach ($slides as $slide) {
            $slot = (int) $slide->sort_order;
            if ($slot >= 0 && $slot < $maxSlides && $grid[$slot] === null) {
                $grid[$slot] = $slide;

                continue;
            }

            for ($i = 0; $i < $maxSlides; $i++) {
                if ($grid[$i] === null) {
                    $grid[$i] = $slide;
                    break;
                }
            }
        }

        return $grid;
    }

    public function update(Request $request, CintaSlide $cintaSlide): RedirectResponse
    {
        $request->validate([
            'alt' => ['nullable', 'string', 'max:160'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $cintaSlide->update([
            'alt' => $request->string('alt')->toString() ?: null,
            'link_url' => $request->string('link_url')->toString() ?: null,
            'sort_order' => $request->integer('sort_order', $cintaSlide->sort_order),
        ]);

        return redirect()
            ->route('admin.cinta.edit')
            ->with('success', 'Diapositiva actualizada.');
    }

    public function destroy(CintaSlide $cintaSlide): RedirectResponse
    {
        $cintaSlide->deleteImageFile();
        $cintaSlide->delete();

        return redirect()
            ->route('admin.cinta.edit')
            ->with('success', 'Imagen eliminada de la cinta.');
    }
}
