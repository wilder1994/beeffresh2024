@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
    $isEdit = $user !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
    <div class="md:col-span-2 flex flex-wrap items-start gap-3 p-3 rounded-lg bg-stone-50 border border-stone-200">
        <div class="flex items-center gap-2.5 shrink-0">
            @if($isEdit && $user)
                <x-user-avatar :user="$user" size="h-14 w-14" class="ring-2 ring-[var(--bf-red)]/20" />
            @else
                <div class="h-14 w-14 rounded-full bg-stone-200 flex items-center justify-center text-stone-500 text-sm font-semibold ring-2 ring-[var(--bf-red)]/15">?</div>
            @endif
            <div class="min-w-0">
                <span class="bf-label-muted normal-case">Foto de perfil</span>
                <span class="text-[11px] text-stone-500 leading-tight block">Opcional · cambiable en Mi perfil</span>
            </div>
        </div>
        <div class="flex-1 min-w-[min(100%,220px)]">
            <input type="file" name="avatar" accept="image/*" class="bf-file" />
            @error('avatar')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="bf-label" for="user-name">Nombre</label>
        <input id="user-name" type="text" name="name" value="{{ old('name', $user?->name) }}" required class="bf-input" />
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
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
        <div class="md:col-span-2">
            <label class="bf-label" for="user-password-new">Nueva contraseña (opcional)</label>
            <input id="user-password-new" type="password" name="password" class="bf-input" autocomplete="new-password" />
            @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            <label class="bf-label mt-2" for="user-password-confirm-new">Confirmar nueva contraseña</label>
            <input id="user-password-confirm-new" type="password" name="password_confirmation" class="bf-input" autocomplete="new-password" placeholder="Repetir nueva contraseña" />
        </div>
    @endif

    <div class="md:col-span-2">
        <label class="bf-label" for="user-role">Rol</label>
        <select id="user-role" name="role" class="bf-select" required>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>{{ $role->label() }} · {{ $role->audienceLabel() }}</option>
            @endforeach
        </select>
        @error('role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
    <div class="md:col-span-2">
        <label class="bf-label" for="user-company-name">Empresa (proveedor)</label>
        <input id="user-company-name" type="text" name="company_name" value="{{ old('company_name', $user?->company_name) }}" class="bf-input" />
        @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="bf-label" for="user-address1">Dirección línea 1</label>
        <input id="user-address1" type="text" name="address_line1" value="{{ old('address_line1', $user?->address_line1) }}" class="bf-input" />
        @error('address_line1')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="bf-label" for="user-address2">Dirección línea 2</label>
        <input id="user-address2" type="text" name="address_line2" value="{{ old('address_line2', $user?->address_line2) }}" class="bf-input" />
        @error('address_line2')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
    <div class="md:col-span-2">
        <label class="bf-label" for="user-delivery-notes">Indicaciones entrega</label>
        <textarea id="user-delivery-notes" name="delivery_instructions" rows="2" class="bf-textarea min-h-[3.25rem]">{{ old('delivery_instructions', $user?->delivery_instructions) }}</textarea>
        @error('delivery_instructions')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
