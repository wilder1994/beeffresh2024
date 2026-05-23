<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreHighlight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreHighlightController extends Controller
{
    public function index(): View
    {
        $highlights = StoreHighlight::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.store.highlights.index', compact('highlights'));
    }

    public function create(): View
    {
        return view('admin.store.highlights.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = basename($request->file('image')->store('store-highlights', 'public'));
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        StoreHighlight::query()->create($data);

        return redirect()->route('admin.store.highlights.index')->with('success', 'Destacado creado.');
    }

    public function edit(StoreHighlight $highlight): View
    {
        return view('admin.store.highlights.edit', compact('highlight'));
    }

    public function update(Request $request, StoreHighlight $highlight): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $highlight->deleteImageFromDisk();
            $data['image'] = basename($request->file('image')->store('store-highlights', 'public'));
        } else {
            unset($data['image']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? $highlight->sort_order);

        $highlight->update($data);

        return redirect()->route('admin.store.highlights.index')->with('success', 'Destacado actualizado.');
    }

    public function destroy(StoreHighlight $highlight): RedirectResponse
    {
        $highlight->deleteImageFromDisk();
        $highlight->delete();

        return redirect()->route('admin.store.highlights.index')->with('success', 'Destacado eliminado.');
    }
}
