@props([
    'banner' => null,
])

<div>
    <label for="banner-title" class="bf-label">Título</label>
    <input
        type="text"
        name="title"
        id="banner-title"
        class="bf-input"
        value="{{ old('title', $banner?->title) }}"
        required
    >
    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="banner-description" class="bf-label">Descripción</label>
    <textarea name="description" id="banner-description" class="bf-textarea" rows="3">{{ old('description', $banner?->description) }}</textarea>
    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:items-stretch">
    <div class="space-y-3">
        <div>
            <label for="banner-link" class="bf-label">Enlace</label>
            <input
                type="url"
                name="link"
                id="banner-link"
                class="bf-input"
                value="{{ old('link', $banner?->link) }}"
                placeholder="https://…"
            >
            @error('link')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="banner-sort-order" class="bf-label">Orden</label>
            <input
                type="number"
                name="sort_order"
                id="banner-sort-order"
                class="bf-input"
                min="0"
                value="{{ old('sort_order', $banner?->sort_order ?? 0) }}"
            >
            @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <label class="bf-form-check-item">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $banner?->is_active ?? true))
            >
            Activo
        </label>
        @error('is_active')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <x-bf.image-upload-zone
        input-id="banner-image-upload"
        :current-url="$banner?->imageUrl()"
        :show-hint="false"
        class="h-full min-h-0"
    />
    @error('image')<p class="md:col-span-2 mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<p class="text-[11px] text-[var(--bf-muted)] -mt-1">JPG, PNG o WebP · recomendado 1200×675 px</p>
