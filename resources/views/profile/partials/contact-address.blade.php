@php
    $cp = $user->customerProfile;
    $sp = $user->supplierProfile;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    @if($user->isSupplier())
        <div class="sm:col-span-2">
            <label class="bf-label" for="supplier_company_name">Empresa</label>
            <input id="supplier_company_name" name="supplier_company_name" type="text" class="bf-input" value="{{ old('supplier_company_name', $sp?->company_name) }}" />
            @error('supplier_company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="supplier_nit">NIT</label>
            <input id="supplier_nit" name="supplier_nit" type="text" class="bf-input" value="{{ old('supplier_nit', $sp?->nit) }}" required />
            @error('supplier_nit')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="supplier_contact_name">Contacto</label>
            <input id="supplier_contact_name" name="supplier_contact_name" type="text" class="bf-input" value="{{ old('supplier_contact_name', $sp?->contact_name) }}" />
        </div>
        <div>
            <label class="bf-label" for="supplier_business_phone">Tel. comercial</label>
            <input id="supplier_business_phone" name="supplier_business_phone" type="text" class="bf-input" value="{{ old('supplier_business_phone', $sp?->business_phone) }}" />
        </div>
        <div>
            <label class="bf-label" for="supplier_business_email">Correo comercial</label>
            <input id="supplier_business_email" name="supplier_business_email" type="email" class="bf-input" value="{{ old('supplier_business_email', $sp?->business_email) }}" />
        </div>
        <div class="sm:col-span-2">
            <label class="bf-label" for="supplier_business_address">Dirección</label>
            <input id="supplier_business_address" name="supplier_business_address" type="text" class="bf-input" value="{{ old('supplier_business_address', $sp?->business_address) }}" />
        </div>
        <div>
            <label class="bf-label" for="supplier_city">Ciudad</label>
            <input id="supplier_city" name="supplier_city" type="text" class="bf-input" value="{{ old('supplier_city', $sp?->city) }}" />
        </div>
    @endif

    <div>
        <label class="bf-label" for="phone">Teléfono</label>
        <input id="phone" name="phone" type="text" class="bf-input" value="{{ old('phone', $user->phone) }}" autocomplete="tel" />
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="document_number">Identificación</label>
        <input id="document_number" name="document_number" type="text" class="bf-input" value="{{ old('document_number', $user->document_number) }}" />
        @error('document_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    @if($user->isCustomer())
        <div class="sm:col-span-2">
            <label class="bf-label" for="customer_address">Dirección</label>
            <input id="customer_address" name="customer_address" type="text" class="bf-input" value="{{ old('customer_address', $cp?->address) }}" />
            @error('customer_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="customer_neighborhood">Barrio</label>
            <input id="customer_neighborhood" name="customer_neighborhood" type="text" class="bf-input" value="{{ old('customer_neighborhood', $cp?->neighborhood) }}" />
        </div>
        <div>
            <label class="bf-label" for="customer_city">Ciudad</label>
            <input id="customer_city" name="customer_city" type="text" class="bf-input" value="{{ old('customer_city', $cp?->city) }}" />
            @error('customer_city')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="customer_state">Provincia</label>
            <input id="customer_state" name="customer_state" type="text" class="bf-input" value="{{ old('customer_state', $cp?->state) }}" />
            @error('customer_state')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="customer_postal_code">C.P.</label>
            <input id="customer_postal_code" name="customer_postal_code" type="text" class="bf-input" value="{{ old('customer_postal_code', $cp?->postal_code) }}" />
        </div>
        <div>
            <label class="bf-label" for="customer_country">País</label>
            <input id="customer_country" name="customer_country" type="text" maxlength="2" class="bf-input" value="{{ old('customer_country', $cp?->country ?? 'CO') }}" />
        </div>
        <div class="sm:col-span-2">
            <label class="bf-label" for="customer_delivery_notes">Indicaciones entrega</label>
            <textarea id="customer_delivery_notes" name="customer_delivery_notes" rows="2" class="bf-textarea min-h-[3rem]">{{ old('customer_delivery_notes', $cp?->delivery_notes) }}</textarea>
        </div>
    @endif
</div>

