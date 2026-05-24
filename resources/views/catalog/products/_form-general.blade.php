@php
    use App\Domain\Catalog\ProductStatus;
    /** @var \App\Models\Product|null $product */
    $product = $product ?? null;
@endphp

<section class="bf-form-panel space-y-3">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">General</h2>

    <div>
        <label class="bf-label" for="name">Nombre</label>
        <input id="name" type="text" name="name" class="bf-input" required x-model="previewName">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="bf-label" for="description">Descripción</label>
        <textarea id="description" name="description" class="bf-textarea" rows="3">{{ old('description', $product?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:items-stretch">
        <div class="space-y-3">
            <div>
                <label class="bf-label" for="status">Estado</label>
                <select id="status" name="status" class="bf-select" required>
                    @foreach(ProductStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $product?->status?->value ?? ProductStatus::Available->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="bf-form-check-item">
                <input type="checkbox" name="featured" value="1" @checked(old('featured', $product?->featured))>
                <span>Destacado en tienda</span>
            </label>
            <label class="bf-form-check-item">
                <input type="checkbox" name="show_on_cinta" value="1" @checked(old('show_on_cinta', $product?->show_on_cinta))>
                <span>Mostrar en cinta (inicio)</span>
            </label>
            @error('featured')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <x-bf.image-upload-zone
            input-id="product-image-upload"
            name="image"
            :current-url="$product?->imageUrl()"
            :show-hint="false"
            class="h-full min-h-0"
        />
        @error('image')<p class="md:col-span-2 mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <p class="text-[11px] text-[var(--bf-muted)] -mt-1">JPG, PNG o WebP · recomendado cuadrada o 4:3 para catálogo</p>
</section>
