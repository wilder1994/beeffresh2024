<div class="max-w-md space-y-4">
    <p class="text-sm text-stone-600">Esta acción es permanente. Confirma con tu contraseña.</p>

    <button
        type="button"
        class="btn btn-sm bg-red-600 hover:bg-red-700 text-white border-0"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-stone-900">¿Eliminar tu cuenta?</h2>
            <p class="mt-2 text-sm text-stone-600">Introduce tu contraseña para confirmar.</p>

            <div class="mt-4">
                <label class="bf-label" for="password">Contraseña</label>
                <input id="password" name="password" type="password" class="bf-input" autocomplete="current-password" />
                @error('password', 'userDeletion')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="bf-btn-ghost" x-on:click="$dispatch('close')">Cancelar</button>
                <button type="submit" class="btn btn-sm bg-red-600 text-white border-0">Eliminar</button>
            </div>
        </form>
    </x-modal>
</div>

