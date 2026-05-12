@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
    $isEdit = $user !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
    @php
        $adminAvatarSrc = ($isEdit && $user && $user->avatar_path) ? $user->avatarUrl() : null;
    @endphp
    <div class="md:col-span-2 flex flex-col items-center gap-2.5 py-2">
        <div class="relative shrink-0">
            <div class="relative h-28 w-28 rounded-full overflow-hidden ring-2 ring-stone-200 bg-stone-100 shadow-inner">
                <img
                    id="admin-user-avatar-preview"
                    src="{{ $adminAvatarSrc ?? '' }}"
                    alt=""
                    class="h-full w-full object-cover {{ $adminAvatarSrc ? '' : 'hidden' }}"
                    width="112"
                    height="112"
                />
                <div
                    id="admin-user-avatar-placeholder"
                    class="absolute inset-0 flex items-center justify-center bg-stone-100 {{ $adminAvatarSrc ? 'hidden' : '' }}"
                    aria-hidden="true"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <label for="admin-user-avatar-input" class="btn btn-circle btn-xs bg-white text-[var(--bf-rust-deep)] border-0 shadow-md hover:bg-[var(--bf-cream)] cursor-pointer absolute -bottom-0.5 -right-0.5" title="Elegir foto de perfil">
                <span class="sr-only">Elegir foto de perfil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </label>
            <input type="file" name="avatar" id="admin-user-avatar-input" class="hidden" accept="image/*" />
        </div>
        <p class="text-center text-[11px] text-stone-500 leading-snug max-w-sm">Opcional. El usuario puede cambiarla después en <span class="font-medium text-stone-600">Mi perfil</span>.</p>
        @error('avatar')<p class="text-xs text-red-600 text-center">{{ $message }}</p>@enderror
    </div>
    <script>
        (function () {
            var input = document.getElementById('admin-user-avatar-input');
            var preview = document.getElementById('admin-user-avatar-preview');
            var placeholder = document.getElementById('admin-user-avatar-placeholder');
            if (!input || !preview || !placeholder) return;
            var lastBlob = null;
            input.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;
                if (lastBlob) {
                    URL.revokeObjectURL(lastBlob);
                    lastBlob = null;
                }
                lastBlob = URL.createObjectURL(file);
                preview.src = lastBlob;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            });
        })();
    </script>

    <div>
        <label class="bf-label" for="user-name">Nombre</label>
        <input id="user-name" type="text" name="name" value="{{ old('name', $user?->name) }}" required class="bf-input" />
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="bf-label" for="user-email">Correo</label>
        <input id="user-email" type="email" name="email" value="{{ old('email', $user?->email) }}" required class="bf-input" />
        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    @if(!$isEdit)
        <div>
            <label class="bf-label" for="user-password">Contraseña</label>
            <input id="user-password" type="password" name="password" required class="bf-input" autocomplete="new-password" />
            @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="user-password-confirmation">Confirmar contraseña</label>
            <input id="user-password-confirmation" type="password" name="password_confirmation" required class="bf-input" autocomplete="new-password" />
        </div>
    @else
        <div>
            <label class="bf-label" for="user-password-new">Nueva contraseña (opcional)</label>
            <input id="user-password-new" type="password" name="password" class="bf-input" autocomplete="new-password" />
            @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="user-password-confirm-new">Confirmar nueva contraseña</label>
            <input id="user-password-confirm-new" type="password" name="password_confirmation" class="bf-input" autocomplete="new-password" placeholder="Repetir nueva contraseña" />
        </div>
    @endif

    <div>
        <label class="bf-label" for="user-role">Rol</label>
        <select id="user-role" name="role" class="bf-select" required>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>{{ $role->label() }} · {{ $role->audienceLabel() }}</option>
            @endforeach
        </select>
        @error('role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        <p class="md:hidden text-[11px] text-stone-500 mt-1.5 leading-snug">Cliente: tienda y checkout. Personal: panel interno. Proveedor: portal de pedidos.</p>
    </div>
    <div class="hidden md:flex flex-col justify-end pb-0.5">
        <p class="text-[11px] text-stone-500 leading-snug">Cliente: tienda y checkout. Personal: panel interno. Proveedor: portal de pedidos.</p>
    </div>
</div>

<p class="bf-form-section-title mt-5">Contacto y domicilio</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
    <div>
        <label class="bf-label" for="user-phone">Teléfono</label>
        <input id="user-phone" type="text" name="phone" value="{{ old('phone', $user?->phone) }}" class="bf-input" />
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-document">Cédula / RNC</label>
        <input id="user-document" type="text" name="document_number" value="{{ old('document_number', $user?->document_number) }}" class="bf-input" />
        @error('document_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-company-name">Empresa (proveedor)</label>
        <input id="user-company-name" type="text" name="company_name" value="{{ old('company_name', $user?->company_name) }}" class="bf-input" />
        @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-address1">Dirección línea 1</label>
        <input id="user-address1" type="text" name="address_line1" value="{{ old('address_line1', $user?->address_line1) }}" class="bf-input" />
        @error('address_line1')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-address2">Dirección línea 2</label>
        <input id="user-address2" type="text" name="address_line2" value="{{ old('address_line2', $user?->address_line2) }}" class="bf-input" />
        @error('address_line2')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-delivery-notes">Indicaciones entrega</label>
        <textarea id="user-delivery-notes" name="delivery_instructions" rows="2" class="bf-textarea min-h-[3.25rem]">{{ old('delivery_instructions', $user?->delivery_instructions) }}</textarea>
        @error('delivery_instructions')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-city">Ciudad</label>
        <input id="user-city" type="text" name="city" value="{{ old('city', $user?->city) }}" class="bf-input" />
        @error('city')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-state">Provincia</label>
        <input id="user-state" type="text" name="state" value="{{ old('state', $user?->state) }}" class="bf-input" />
        @error('state')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-postal">Código postal</label>
        <input id="user-postal" type="text" name="postal_code" value="{{ old('postal_code', $user?->postal_code) }}" class="bf-input" />
        @error('postal_code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="user-country">País (ISO-2)</label>
        <input id="user-country" type="text" name="country" maxlength="2" value="{{ old('country', $user?->country ?? 'DO') }}" class="bf-input" />
        @error('country')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
