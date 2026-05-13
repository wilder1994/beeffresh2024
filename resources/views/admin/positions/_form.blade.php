@php
    /** @var \App\Models\Position|null $position */
    $position = $position ?? null;
@endphp

<div class="space-y-3">
    <div>
        <label for="name" class="bf-label normal-case">Nombre</label>
        <input type="text" name="name" id="name" value="{{ old('name', $position?->name) }}" required maxlength="191" class="bf-input" />
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="slug" class="bf-label-muted normal-case">Slug (opcional; se genera desde el nombre)</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $position?->slug) }}" maxlength="191" class="bf-input font-mono text-sm" />
        @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="bf-label-muted normal-case">Descripción</label>
        <textarea name="description" id="description" rows="3" maxlength="2000" class="bf-textarea min-h-[5rem]">{{ old('description', $position?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="status" class="bf-label normal-case">Estado</label>
        <select name="status" id="status" class="bf-select" required>
            <option value="active" @selected(old('status', $position?->status ?? 'active') === 'active')>Activo</option>
            <option value="inactive" @selected(old('status', $position?->status) === 'inactive')>Inactivo</option>
        </select>
        @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
