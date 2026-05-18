@props([
    'formId' => 'profile-update-form',
    'inputId' => 'profile-avatar-input',
    'size' => 'h-16 w-16',
    'forLivewire' => false,
])

<section class="relative shrink-0">
    <section @class([$size, 'rounded-full overflow-hidden ring-2 ring-[var(--bf-brand)]/25 bg-stone-100 flex items-center justify-center'])>
        <img
            x-show="preview"
            x-bind:src="preview"
            alt="Foto de perfil"
            class="h-full w-full object-cover"
            x-cloak
        />
        <span
            x-show="!preview"
            x-cloak
            x-text="initial"
            class="text-xl font-bold text-stone-400 select-none"
            aria-hidden="true"
        ></span>
    </section>
    <input
        type="file"
        id="{{ $inputId }}"
        @unless($forLivewire) name="avatar" form="{{ $formId }}" @endunless
        class="sr-only"
        accept="image/jpeg,image/png,image/webp,image/gif"
        x-on:change="pickFile($event)"
    />
    <label
        for="{{ $inputId }}"
        class="absolute bottom-0 right-0 btn btn-circle btn-xs bg-[var(--bf-brand)] text-white border-0 shadow-md cursor-pointer"
        title="Cambiar foto"
    >
        <span class="sr-only">Elegir foto</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
    </label>
</section>
