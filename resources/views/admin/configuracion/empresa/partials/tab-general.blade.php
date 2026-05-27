<form
    method="post"
    action="{{ route('admin.configuracion.empresa.general') }}"
    enctype="multipart/form-data"
    class="bf-form-panel bf-form-panel-tight space-y-4"
>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-4 items-start">
        <x-bf.image-upload-zone
            name="logo"
            input-id="company-settings-logo"
            :current-url="$logoUrl"
            label="Logo"
            crop-profile="logo"
            :show-hint="true"
        />
        <p class="text-xs text-[var(--bf-muted)] sm:pt-8 leading-relaxed">
            Se muestra en el panel interno y en la tienda. Formato cuadrado; al guardar se actualiza en todo el sistema.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="trade_name" class="bf-label normal-case">Nombre comercial</label>
            <input type="text" name="trade_name" id="trade_name" value="{{ old('trade_name', $profile->trade_name) }}" maxlength="191" class="bf-input" placeholder="BEEF FRESH" />
            @error('trade_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="legal_name" class="bf-label normal-case">Razón social</label>
            <input type="text" name="legal_name" id="legal_name" value="{{ old('legal_name', $profile->legal_name) }}" maxlength="191" class="bf-input" />
            @error('legal_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="nit" class="bf-label normal-case">NIT</label>
            <input type="text" name="nit" id="nit" value="{{ old('nit', $profile->nit) }}" maxlength="64" class="bf-input" />
            @error('nit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="contact_phone" class="bf-label normal-case">Teléfono de contacto</label>
            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $profile->contact_phone) }}" maxlength="32" class="bf-input" />
            @error('contact_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="contact_email" class="bf-label normal-case">Correo de contacto</label>
            <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $profile->contact_email) }}" maxlength="191" class="bf-input" />
            @error('contact_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="bf-form-actions justify-end">
        <button type="submit" class="bf-btn-primary">Guardar general</button>
    </div>
</form>
