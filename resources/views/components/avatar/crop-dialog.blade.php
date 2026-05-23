{{-- Debe estar dentro de un ancestro con x-data="avatarEditor(...)" (sin teleport: Livewire rompe el scope). --}}
<div
    x-show="cropOpen"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="bf-avatar-crop-title"
    x-on:keydown.escape.window.stop="cancelCrop()"
>
    <div class="absolute inset-0 bg-stone-900/60" x-on:click="cancelCrop()" aria-hidden="true"></div>

    <div
        class="relative w-full max-w-md bg-white rounded-xl shadow-2xl p-5 sm:p-6"
        x-on:click.stop
        x-transition
    >
        <header class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h3 id="bf-avatar-crop-title" class="text-base font-bold text-stone-900">Ajustar foto</h3>
                <p class="text-xs text-stone-500 mt-0.5">Arrastra para centrar · Gira o amplía dentro del círculo</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-circle shrink-0" x-on:click="cancelCrop()" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </header>

        <div class="bf-avatar-crop__stage mx-auto">
            <canvas
                x-ref="cropCanvas"
                class="bf-avatar-crop__canvas touch-none cursor-grab active:cursor-grabbing"
                width="280"
                height="280"
                x-on:pointerdown="pointerDown($event)"
                x-on:pointermove="pointerMove($event)"
                x-on:pointerup="pointerUp($event)"
                x-on:pointercancel="pointerUp($event)"
            ></canvas>
            <div class="bf-avatar-crop__ring pointer-events-none" aria-hidden="true"></div>
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
            <button type="button" class="bf-btn-primary btn-sm" x-on:click="applyCrop()" x-bind:disabled="applying">
                <span x-show="!applying">Usar foto</span>
                <span x-show="applying" x-cloak>Procesando…</span>
            </button>
        </footer>
    </div>
</div>
