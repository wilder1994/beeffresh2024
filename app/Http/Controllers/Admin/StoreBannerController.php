<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreBannerController extends Controller
{
    public function index(): View
    {
        $banners = StoreBanner::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.store.banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.store.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'link' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = basename($request->file('image')->store('store-banners', 'public'));
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        StoreBanner::query()->create($data);

        return redirect()->route('admin.store.banners.index')->with('success', 'Banner creado.');
    }

    public function edit(StoreBanner $banner): View
    {
        return view('admin.store.banners.edit', compact('banner'));
    }

    public function update(Request $request, StoreBanner $banner): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'link' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $banner->deleteImageFromDisk();
            $data['image'] = basename($request->file('image')->store('store-banners', 'public'));
        } else {
            unset($data['image']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? $banner->sort_order);

        $banner->update($data);

        return redirect()->route('admin.store.banners.index')->with('success', 'Banner actualizado.');
    }

    public function destroy(StoreBanner $banner): RedirectResponse
    {
        $banner->deleteImageFromDisk();
        $banner->delete();

        return redirect()->route('admin.store.banners.index')->with('success', 'Banner eliminado.');
    }
}
