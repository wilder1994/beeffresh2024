<section>
    <header class="mb-4">
        <h2 class="text-lg font-medium text-gray-900">
            Contacto y entrega
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            @if($user->isCustomer())
                Datos necesarios para pedidos en línea y domicilios.
            @elseif($user->isSupplier())
                Datos comerciales del proveedor.
            @else
                Información de contacto opcional para personal interno.
            @endif
        </p>
    </header>

    @php
        $cp = $user->customerProfile;
        $sp = $user->supplierProfile;
    @endphp

    <div class="space-y-3">
        @if($user->isSupplier())
            <div>
                <x-input-label for="supplier_company_name" value="Empresa / razón social" />
                <x-text-input id="supplier_company_name" name="supplier_company_name" type="text" :value="old('supplier_company_name', $sp?->company_name)" autocomplete="organization" />
                <x-input-error class="mt-2" :messages="$errors->get('supplier_company_name')" />
            </div>
            <div>
                <x-input-label for="supplier_nit" value="NIT / documento fiscal" />
                <x-text-input id="supplier_nit" name="supplier_nit" type="text" :value="old('supplier_nit', $sp?->nit)" required />
                <x-input-error class="mt-2" :messages="$errors->get('supplier_nit')" />
            </div>
            <div>
                <x-input-label for="supplier_contact_name" value="Nombre de contacto" />
                <x-text-input id="supplier_contact_name" name="supplier_contact_name" type="text" :value="old('supplier_contact_name', $sp?->contact_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('supplier_contact_name')" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <x-input-label for="supplier_business_phone" value="Teléfono comercial" />
                    <x-text-input id="supplier_business_phone" name="supplier_business_phone" type="text" :value="old('supplier_business_phone', $sp?->business_phone)" />
                    <x-input-error class="mt-2" :messages="$errors->get('supplier_business_phone')" />
                </div>
                <div>
                    <x-input-label for="supplier_business_email" value="Correo comercial" />
                    <x-text-input id="supplier_business_email" name="supplier_business_email" type="email" :value="old('supplier_business_email', $sp?->business_email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('supplier_business_email')" />
                </div>
            </div>
            <div>
                <x-input-label for="supplier_business_address" value="Dirección fiscal / oficina" />
                <x-text-input id="supplier_business_address" name="supplier_business_address" type="text" :value="old('supplier_business_address', $sp?->business_address)" />
                <x-input-error class="mt-2" :messages="$errors->get('supplier_business_address')" />
            </div>
            <div>
                <x-input-label for="supplier_city" value="Ciudad" />
                <x-text-input id="supplier_city" name="supplier_city" type="text" :value="old('supplier_city', $sp?->city)" />
                <x-input-error class="mt-2" :messages="$errors->get('supplier_city')" />
            </div>
        @endif

        <div>
            <x-input-label for="phone" value="Teléfono (WhatsApp preferible)" />
            <x-text-input id="phone" name="phone" type="text" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="document_number" value="Cédula / RNC / identificación" />
            <x-text-input id="document_number" name="document_number" type="text" :value="old('document_number', $user->document_number)" />
            <x-input-error class="mt-2" :messages="$errors->get('document_number')" />
        </div>

        @if($user->isCustomer())
            <div>
                <x-input-label for="customer_address" value="Dirección (calle y número)" />
                <x-text-input id="customer_address" name="customer_address" type="text" :value="old('customer_address', $cp?->address)" autocomplete="street-address" />
                <x-input-error class="mt-2" :messages="$errors->get('customer_address')" />
            </div>
            <div>
                <x-input-label for="customer_neighborhood" value="Sector / barrio / referencia" />
                <x-text-input id="customer_neighborhood" name="customer_neighborhood" type="text" :value="old('customer_neighborhood', $cp?->neighborhood)" />
                <x-input-error class="mt-2" :messages="$errors->get('customer_neighborhood')" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <x-input-label for="customer_city" value="Ciudad / municipio" />
                    <x-text-input id="customer_city" name="customer_city" type="text" :value="old('customer_city', $cp?->city)" autocomplete="address-level2" />
                    <x-input-error class="mt-2" :messages="$errors->get('customer_city')" />
                </div>
                <div>
                    <x-input-label for="customer_state" value="Provincia / estado" />
                    <x-text-input id="customer_state" name="customer_state" type="text" :value="old('customer_state', $cp?->state)" autocomplete="address-level1" />
                    <x-input-error class="mt-2" :messages="$errors->get('customer_state')" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <x-input-label for="customer_postal_code" value="Código postal (opcional)" />
                    <x-text-input id="customer_postal_code" name="customer_postal_code" type="text" :value="old('customer_postal_code', $cp?->postal_code)" autocomplete="postal-code" />
                    <x-input-error class="mt-2" :messages="$errors->get('customer_postal_code')" />
                </div>
                <div>
                    <x-input-label for="customer_country" value="País (ISO 2 letras)" />
                    <x-text-input id="customer_country" name="customer_country" type="text" maxlength="2" :value="old('customer_country', $cp?->country ?? 'DO')" autocomplete="country" />
                    <x-input-error class="mt-2" :messages="$errors->get('customer_country')" />
                </div>
            </div>
            <div>
                <x-input-label for="customer_delivery_notes" value="Indicaciones para el domiciliario" />
                <textarea id="customer_delivery_notes" name="customer_delivery_notes" rows="3" class="bf-textarea min-h-[4rem]">{{ old('customer_delivery_notes', $cp?->delivery_notes) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('customer_delivery_notes')" />
            </div>
        @endif
    </div>
</section>
