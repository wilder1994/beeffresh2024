<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreMeatTypeRequest;
use App\Http\Requests\Catalog\UpdateMeatTypeRequest;
use App\Models\MeatType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MeatTypeController extends Controller
{
    public function index(): View
    {
        $meatTypes = MeatType::query()
            ->withCount(['meatCuts', 'products'])
            ->orderBy('name')
            ->get();

        return view('catalog.meat-types.index', compact('meatTypes'));
    }

    public function store(StoreMeatTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) $data['name']);

        MeatType::query()->create($data);

        return redirect()
            ->route('catalog.meat-types.index')
            ->with('success', 'Tipo de carne creado.');
    }

    public function update(UpdateMeatTypeRequest $request, MeatType $meatType): RedirectResponse
    {
        $data = $request->validated();

        if ($meatType->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug((string) $data['name'], $meatType->id);
        }

        $meatType->update($data);

        return redirect()
            ->route('catalog.meat-types.index')
            ->with('success', 'Tipo de carne actualizado.');
    }

    public function destroy(MeatType $meatType): RedirectResponse
    {
        if ($meatType->products()->exists()) {
            return redirect()
                ->route('catalog.meat-types.index')
                ->with('error', 'No se puede eliminar: hay productos con este tipo.');
        }

        $meatType->delete();

        return redirect()
            ->route('catalog.meat-types.index')
            ->with('success', 'Tipo de carne eliminado.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (MeatType::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
