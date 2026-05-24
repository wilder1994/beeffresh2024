{{-- Debe estar dentro de un ancestro con Alpine crop (imageCropUpload, avatarEditor, logoCropUpload). --}}
<div
    x-show="cropOpen"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:px-4 sm:py-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="bf-image-crop-title"
    x-on:keydown.escape.window.stop="cancelCrop()"
>
    <div class="absolute inset-0 bg-stone-900/60" x-on:click="cancelCrop()" aria-hidden="true"></div>

    <div
        class="relative w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-xl shadow-2xl p-5 sm:p-6 max-h-[96dvh] overflow-y-auto"
        x-on:click.stop
        x-transition
    >
        <header class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h3 id="bf-image-crop-title" class="text-base font-bold text-stone-900" x-text="cropTitle || 'Ajustar imagen'"></h3>
                <p class="text-xs text-stone-500 mt-0.5" x-text="cropSubtitle || 'Arrastra, haz zoom o gira'"></p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-circle shrink-0" x-on:click="cancelCrop()" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </header>

        <div
            class="bf-image-crop__stage mx-auto"
            x-bind:class="cropVariant === 'circle' ? 'bf-image-crop__stage--circle' : 'bf-image-crop__stage--rect'"
            x-bind:style="'--bf-crop-aspect: ' + (cropAspectStyle || '4/3')"
        >
            <canvas
                x-ref="cropCanvas"
                class="bf-image-crop__canvas touch-none cursor-grab active:cursor-grabbing"
                x-bind:width="viewportW"
                x-bind:height="viewportH"
                x-on:pointerdown="pointerDown($event)"
                x-on:pointermove="pointerMove($event)"
                x-on:pointerup="pointerUp($event)"
                x-on:pointercancel="pointerUp($event)"
            ></canvas>
            <div
                class="bf-image-crop__frame pointer-events-none"
                x-bind:class="cropVariant === 'circle' ? 'bf-image-crop__frame--circle' : 'bf-image-crop__frame--rect'"
                aria-hidden="true"
            ></div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="rotateLeft()" title="Girar izquierda">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4" /></svg>
                <span class="sr-only">Girar izquierda</span>
            </button>
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="zoomOut()" title="Alejar">−</button>
            <input
                type="range"
                min="0.5"
                max="3"
                step="0.02"
                class="range range-xs w-28 [--range-bg:var(--bf-cream)] [--range-fill:var(--bf-brand)]"
                x-bind:value="scaleMul"
                x-on:input="onZoomInput($event)"
                aria-label="Zoom"
            />
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="zoomIn()" title="Acercar">+</button>
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="rotateRight()" title="Girar derecha">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4" /></svg>
                <span class="sr-only">Girar derecha</span>
            </button>
        </div>

        <footer class="mt-5 flex flex-wrap gap-2 justify-end border-t border-stone-100 pt-4">
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="cancelCrop()" x-bind:disabled="applying">Cancelar</button>
            <button type="button" class="bf-btn-primary btn-sm" x-on:click.stop="applyCrop()" x-bind:disabled="applying">
                <span x-show="!applying">Usar imagen</span>
                <span x-show="applying" x-cloak>Procesando…</span>
            </button>
        </footer>
    </div>
</div>
