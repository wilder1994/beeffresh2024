@php($inModal = $inModal ?? false)
<form method="post" action="{{ route('password.update') }}" class="space-y-4 max-w-md">
    @csrf
    @method('put')
    @if($inModal)
        <input type="hidden" name="_profile_modal" value="1" />
    @endif

    <div>
        <label class="bf-label" for="update_password_current_password">Contraseña actual</label>
        <input id="update_password_current_password" name="current_password" type="password" class="bf-input" autocomplete="current-password" />
        @error('current_password', 'updatePassword')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="bf-label" for="update_password_password">Nueva contraseña</label>
        <input id="update_password_password" name="password" type="password" class="bf-input" autocomplete="new-password" />
        @error('password', 'updatePassword')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="bf-label" for="update_password_password_confirmation">Confirmar</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="bf-input" autocomplete="new-password" />
        @error('password_confirmation', 'updatePassword')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="bf-form-actions border-t border-stone-100 pt-4">
        <button type="submit" class="bf-btn-primary">Actualizar contraseña</button>
        @if (session('status') === 'password-updated')
            <span class="text-xs text-green-700 font-medium">Actualizada</span>
        @endif
    </div>
</form>

