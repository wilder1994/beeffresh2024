<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreMeatCutRequest;
use App\Http\Requests\Catalog\UpdateMeatCutRequest;
use App\Models\MeatCut;
use App\Models\MeatType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MeatCutController extends Controller
{
    public function index(): View
    {
        $meatCuts = MeatCut::query()
            ->with('meatType')
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $meatTypes = MeatType::query()->orderBy('name')->get();

        return view('catalog.meat-cuts.index', compact('meatCuts', 'meatTypes'));
    }

    public function store(StoreMeatCutRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((int) $data['meat_type_id'], (string) $data['name']);

        MeatCut::query()->create($data);

        return redirect()
            ->route('catalog.meat-cuts.index')
            ->with('success', 'Corte creado.');
    }

    public function update(UpdateMeatCutRequest $request, MeatCut $meatCut): RedirectResponse
    {
        $data = $request->validated();

        if ($meatCut->name !== $data['name'] || (int) $data['meat_type_id'] !== $meatCut->meat_type_id) {
            $data['slug'] = $this->uniqueSlug((int) $data['meat_type_id'], (string) $data['name'], $meatCut->id);
        }

        $meatCut->update($data);

        return redirect()
            ->route('catalog.meat-cuts.index')
            ->with('success', 'Corte actualizado.');
    }

    public function destroy(MeatCut $meatCut): RedirectResponse
    {
        if ($meatCut->products()->exists()) {
            return redirect()
                ->route('catalog.meat-cuts.index')
                ->with('error', 'No se puede eliminar: hay productos con este corte.');
        }

        $meatCut->delete();

        return redirect()
            ->route('catalog.meat-cuts.index')
            ->with('success', 'Corte eliminado.');
    }

    private function uniqueSlug(int $meatTypeId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (MeatCut::query()
            ->where('meat_type_id', $meatTypeId)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
