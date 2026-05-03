@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
    $isEdit = $user !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2 flex flex-wrap items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
        <div class="flex items-center gap-3">
            @if($isEdit && $user)
                <x-user-avatar :user="$user" size="h-16 w-16" class="ring-2 ring-[var(--bf-red)]/20" />
            @else
                <div class="h-16 w-16 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xl font-semibold ring-2 ring-[var(--bf-red)]/15">?</div>
            @endif
            <div>
                <span class="label-text font-medium block">Foto de perfil</span>
                <span class="text-xs text-gray-500">Opcional. El usuario puede cambiarla después en Mi perfil.</span>
            </div>
        </div>
        <div class="flex-1 min-w-[200px]">
            <input type="file" name="avatar" accept="image/*" class="file-input file-input-bordered file-input-sm w-full bg-white max-w-md" />
            @error('avatar')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="md:col-span-2">
        <label class="label"><span class="label-text font-medium">Nombre</span></label>
        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required class="input input-bordered w-full bg-white" />
        @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="label"><span class="label-text font-medium">Correo</span></label>
        <input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="input input-bordered w-full bg-white" />
        @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    @if(!$isEdit)
        <div>
            <label class="label"><span class="label-text font-medium">Contraseña</span></label>
            <input type="password" name="password" required class="input input-bordered w-full bg-white" autocomplete="new-password" />
            @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label"><span class="label-text font-medium">Confirmar contraseña</span></label>
            <input type="password" name="password_confirmation" required class="input input-bordered w-full bg-white" autocomplete="new-password" />
        </div>
    @else
        <div class="md:col-span-2">
            <label class="label"><span class="label-text font-medium">Nueva contraseña (opcional)</span></label>
            <input type="password" name="password" class="input input-bordered w-full bg-white" autocomplete="new-password" />
            @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            <input type="password" name="password_confirmation" class="input input-bordered w-full bg-white mt-2" autocomplete="new-password" placeholder="Confirmar nueva contraseña" />
        </div>
    @endif

    <div class="md:col-span-2">
        <label class="label"><span class="label-text font-medium">Rol</span></label>
        <select name="role" class="select select-bordered w-full bg-white" required>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>{{ $role->label() }} · {{ $role->audienceLabel() }}</option>
            @endforeach
        </select>
        @error('role')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<p class="text-sm font-semibold text-gray-700 mt-6 mb-2">Contacto y domicilio</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="label"><span class="label-text">Teléfono</span></label>
        <input type="text" name="phone" value="{{ old('phone', $user?->phone) }}" class="input input-bordered w-full bg-white" />
        @error('phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label"><span class="label-text">Cédula / RNC</span></label>
        <input type="text" name="document_number" value="{{ old('document_number', $user?->document_number) }}" class="input input-bordered w-full bg-white" />
        @error('document_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="label"><span class="label-text">Empresa (proveedor)</span></label>
        <input type="text" name="company_name" value="{{ old('company_name', $user?->company_name) }}" class="input input-bordered w-full bg-white" />
        @error('company_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="label"><span class="label-text">Dirección línea 1</span></label>
        <input type="text" name="address_line1" value="{{ old('address_line1', $user?->address_line1) }}" class="input input-bordered w-full bg-white" />
        @error('address_line1')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="label"><span class="label-text">Dirección línea 2</span></label>
        <input type="text" name="address_line2" value="{{ old('address_line2', $user?->address_line2) }}" class="input input-bordered w-full bg-white" />
        @error('address_line2')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label"><span class="label-text">Ciudad</span></label>
        <input type="text" name="city" value="{{ old('city', $user?->city) }}" class="input input-bordered w-full bg-white" />
        @error('city')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label"><span class="label-text">Provincia</span></label>
        <input type="text" name="state" value="{{ old('state', $user?->state) }}" class="input input-bordered w-full bg-white" />
        @error('state')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label"><span class="label-text">Código postal</span></label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $user?->postal_code) }}" class="input input-bordered w-full bg-white" />
        @error('postal_code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label"><span class="label-text">País (ISO-2)</span></label>
        <input type="text" name="country" maxlength="2" value="{{ old('country', $user?->country ?? 'DO') }}" class="input input-bordered w-full bg-white" />
        @error('country')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="label"><span class="label-text">Indicaciones entrega</span></label>
        <textarea name="delivery_instructions" rows="2" class="textarea textarea-bordered w-full bg-white">{{ old('delivery_instructions', $user?->delivery_instructions) }}</textarea>
        @error('delivery_instructions')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
