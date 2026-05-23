@props([
    'name' => 'image',
    'inputId' => 'bf-image-upload',
    'currentUrl' => null,
    'label' => 'Imagen',
    'hint' => 'JPG, PNG o WebP · recomendado 1200×675 px',
    'showHint' => true,
])

<div
    {{ $attributes->merge(['class' => 'bf-image-upload flex h-full min-h-0 flex-col']) }}
    x-data="{
        initialUrl: @js($currentUrl),
        preview: @js($currentUrl),
        fileName: '',
        dragging: false,
        applyFile(file) {
            if (!file || !file.type.startsWith('image/')) {
                return;
            }
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.preview = event.target?.result ?? this.initialUrl;
            };
            reader.readAsDataURL(file);
        },
        onPick(event) {
            const file = event.target.files?.[0];
            if (file) {
                this.applyFile(file);
            }
        },
        onDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer?.files?.[0];
            if (!file || !this.$refs.input) {
                return;
            }
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.input.files = dt.files;
            this.applyFile(file);
        },
        clearPick() {
            this.preview = this.initialUrl;
            this.fileName = '';
            if (this.$refs.input) {
                this.$refs.input.value = '';
            }
        }
    }"
>
    <span class="bf-label">{{ $label }}</span>

    <input
        id="{{ $inputId }}"
        type="file"
        name="{{ $name }}"
        x-ref="input"
        accept="image/jpeg,image/png,image/webp"
        class="sr-only"
        @change="onPick($event)"
    >

    <label
        for="{{ $inputId }}"
        class="bf-image-upload-zone min-h-[9rem] w-full flex-1"
        :class="{ 'bf-image-upload-zone--drag': dragging }"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop($event)"
    >
        <img
            x-show="preview"
            x-bind:src="preview"
            alt=""
            class="bf-image-upload-zone__preview"
        >

        <span x-show="!preview" class="bf-image-upload-zone__empty">
            <svg class="h-8 w-8 text-[var(--bf-brand)]/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="bf-image-upload-zone__cta">Selecciona imagen</span>
        </span>

        <span x-show="preview" class="bf-image-upload-zone__change">
            Cambiar imagen
        </span>
    </label>

    @if($showHint)
        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 min-h-[1.25rem]">
            <p class="text-[11px] text-[var(--bf-muted)]">{{ $hint }}</p>
            <p x-show="fileName" x-text="fileName" class="text-[11px] text-[var(--bf-ink)] truncate max-w-[12rem]"></p>
            <button
                type="button"
                x-show="fileName"
                x-cloak
                class="text-[11px] font-medium text-[var(--bf-brand)] hover:underline"
                @click="clearPick()"
            >
                Quitar selección
            </button>
        </div>
    @elseif($hint)
        <p x-show="fileName" x-text="fileName" class="mt-2 text-[11px] text-[var(--bf-ink)] truncate"></p>
    @endif
</div>
