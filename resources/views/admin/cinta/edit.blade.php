@extends('layouts.app')

@section('titulo', 'Cinta · Inicio')
@section('cabecera', 'Cinta del inicio')

@section('contenido')
    @php
        $recW = (int) ($spec['recommended_width'] ?? 1920);
        $recH = (int) ($spec['recommended_height'] ?? 400);
        $minW = (int) ($spec['min_width'] ?? 1200);
        $minH = (int) ($spec['min_height'] ?? 250);
        $ratio = $spec['aspect_ratio_label'] ?? '16:9';
    @endphp

    <div
        class="bf-cinta-admin w-full max-w-none mx-auto px-1 sm:px-2 pt-0 pb-2 sm:pb-3"
        x-data="{
            detailSlot: null,
            uploading: false,
            setSlot(slot) {
                if (this.$refs.slotField) {
                    this.$refs.slotField.value = String(slot);
                }
            },
            onFilePicked(event) {
                const input = event.target;
                if (this.uploading || !input?.files?.length) {
                    return;
                }
                this.uploading = true;
                input.form.requestSubmit();
            }
        }"
    >
        @if($errors->any())
            <div class="mb-4 text-sm text-red-800 bg-red-50/90 border border-red-200/80 rounded-lg px-3 py-2 space-y-0.5">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <header class="bf-cinta-admin__intro mb-3 sm:mb-4">
            <p class="text-sm text-[var(--bf-muted)] leading-snug max-w-4xl">
                Haz clic en una casilla vacía para seleccionar una imagen. Recomendado: {{ $recW }}×{{ $recH }} px (formato {{ $ratio }}, ±{{ (int) round(((float) ($spec['aspect_ratio_tolerance'] ?? 0.03)) * 100) }}%), mínimo {{ $minW }}×{{ $minH }} px, en JPG, PNG o WebP, con un máximo de {{ $maxSlides }} imágenes en la
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="text-[var(--bf-brand)] font-medium hover:underline">página de inicio</a>.
            </p>
            <p class="mt-1 text-xs font-medium text-[var(--bf-brand)]/80">
                {{ $slideCount }} de {{ $maxSlides }} casillas en uso
            </p>
        </header>

        <form
            id="cinta-upload-form"
            action="{{ route('admin.cinta.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="bf-cinta-upload-form"
        >
            @csrf
            <input type="hidden" name="slot" x-ref="slotField" value="">
            <input
                id="cinta-file-upload"
                type="file"
                name="imagen"
                x-ref="fileInput"
                accept="image/jpeg,image/png,image/webp"
                tabindex="-1"
                x-on:change="onFilePicked($event)"
            >
        </form>

        <div class="bf-cinta-admin__grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-2.5">
            @foreach($grid as $slot => $slide)
                @if($slide)
                    <article
                        class="bf-cinta-slot bf-cinta-slot--filled group bf-cinta-admin__card w-full min-w-0 rounded-xl overflow-hidden"
                    >
                        <div class="bf-cinta-slot__media">
                            <img
                                src="{{ $slide->imageUrl() }}"
                                alt="{{ $slide->alt ?? 'Diapositiva '.($slot + 1) }}"
                                class="bf-cinta-slot__img"
                            >
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-[var(--bf-rust-deep)]/55 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"></div>

                        <div class="absolute top-1.5 right-1.5 flex gap-1 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity z-10">
                            <label
                                for="cinta-file-upload"
                                class="bf-cinta-slot-btn cursor-pointer"
                                title="Cambiar imagen"
                                x-on:mousedown.stop="setSlot({{ $slot }})"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            </label>
                            <button
                                type="button"
                                class="bf-cinta-slot-btn"
                                title="Texto y enlace"
                                x-on:click.stop="detailSlot = detailSlot === {{ $slot }} ? null : {{ $slot }}"
                                x-bind:class="detailSlot === {{ $slot }} ? 'ring-2 ring-[var(--bf-gold)]' : ''"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>
                            <form action="{{ route('admin.cinta.destroy', $slide) }}" method="POST" class="inline" onsubmit="return confirm('¿Quitar esta imagen de la cinta?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bf-cinta-slot-btn bf-cinta-slot-btn--danger" title="Eliminar">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>

                        <label
                            for="cinta-file-upload"
                            class="absolute inset-0 z-[5] cursor-pointer"
                            aria-label="Cambiar imagen casilla {{ $slot + 1 }}"
                            x-on:mousedown.stop="setSlot({{ $slot }})"
                        ></label>

                        <span class="absolute bottom-1 left-2 text-[10px] font-medium text-white/90 drop-shadow-sm pointer-events-none z-[1]">
                            {{ $slot + 1 }}
                        </span>
                    </article>
                @else
                    <label
                        for="cinta-file-upload"
                        class="bf-cinta-slot bf-cinta-slot--empty bf-cinta-admin__card w-full min-w-0 rounded-xl cursor-pointer"
                        aria-label="Subir imagen casilla {{ $slot + 1 }}"
                        x-on:mousedown.stop="setSlot({{ $slot }})"
                    >
                        <span class="bf-cinta-slot__plus" aria-hidden="true">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg>
                        </span>
                        <span class="bf-cinta-slot__num">{{ $slot + 1 }}</span>
                    </label>
                @endif
            @endforeach
        </div>

        @foreach($grid as $slot => $slide)
            @if($slide)
                <div
                    x-show="detailSlot === {{ $slot }}"
                    x-cloak
                    x-transition
                    class="mt-4 p-4 rounded-xl border border-[var(--bf-brand)]/12 bg-white/60 backdrop-blur-sm"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--bf-muted)] mb-3">Casilla {{ $slot + 1 }} · texto y enlace</p>
                    <form action="{{ route('admin.cinta.update', $slide) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="sort_order" value="{{ $slide->sort_order }}">
                        <div class="sm:col-span-2">
                            <label class="bf-label-muted normal-case text-xs">Texto alternativo</label>
                            <input type="text" name="alt" value="{{ old('alt', $slide->alt) }}" maxlength="160" class="bf-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="bf-label-muted normal-case text-xs">Enlace al hacer clic (opcional)</label>
                            <input type="url" name="link_url" value="{{ old('link_url', $slide->link_url) }}" class="bf-input" placeholder="https://…">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="bf-btn-primary text-sm">Guardar</button>
                        </div>
                    </form>
                </div>
            @endif
        @endforeach
    </div>
@endsection
