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
        <x-forms.colombia-address
            prefix="supplier"
            :required="false"
            :address="old('supplier_business_address', $sp?->business_address)"
            :neighborhood="old('supplier_neighborhood', $sp?->neighborhood)"
            :city="old('supplier_city', $sp?->city)"
            :department="old('supplier_state', $sp?->state)"
            :latitude="old('supplier_latitude', $sp?->latitude)"
            :longitude="old('supplier_longitude', $sp?->longitude)"
            class="sm:col-span-2 !p-0"
        />
    @endif

    <div>
        <label class="bf-label" for="phone">Teléfono</label>
        <input id="phone" name="phone" type="text" class="bf-input" value="{{ old('phone', $user->phone) }}" autocomplete="tel" />
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="document_type">Tipo documento</label>
        <x-forms.document-type-select
            id="document_type"
            name="document_type"
            :legacy-value="old('document_type', $user->document_type)"
        />
        @error('document_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="bf-label" for="document_number">Número documento</label>
        <input id="document_number" name="document_number" type="text" class="bf-input" value="{{ old('document_number', $user->document_number) }}" />
        @error('document_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    @if($user->isCustomer())
        <x-forms.colombia-address
            prefix="customer"
            :address="old('customer_address', $cp?->address)"
            :neighborhood="old('customer_neighborhood', $cp?->neighborhood)"
            :city="old('customer_city', $cp?->city)"
            :department="old('customer_state', $cp?->state)"
            :latitude="old('customer_latitude', $cp?->latitude)"
            :longitude="old('customer_longitude', $cp?->longitude)"
            show-postal
            show-delivery-notes
            class="sm:col-span-2 !p-0"
        >
            <x-slot:postal>
                <div>
                    <label class="bf-label" for="customer_postal_code">C.P.</label>
                    <input id="customer_postal_code" name="customer_postal_code" type="text" class="bf-input" value="{{ old('customer_postal_code', $cp?->postal_code) }}" />
                </div>
            </x-slot:postal>
            <x-slot:deliveryNotes>
                <div class="sm:col-span-2">
                    <label class="bf-label" for="customer_delivery_notes">Indicaciones entrega</label>
                    <textarea id="customer_delivery_notes" name="customer_delivery_notes" rows="2" class="bf-textarea min-h-[3rem]">{{ old('customer_delivery_notes', $cp?->delivery_notes) }}</textarea>
                </div>
            </x-slot:deliveryNotes>
        </x-forms.colombia-address>
    @endif
</div>
