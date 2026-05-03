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

    <div class="space-y-4">
        @if($user->isSupplier())
            <div>
                <x-input-label for="company_name" value="Empresa / razón social" />
                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $user->company_name)" autocomplete="organization" />
                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
            </div>
        @endif

        <div>
            <x-input-label for="phone" value="Teléfono (WhatsApp preferible)" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="document_number" value="Cédula / RNC / identificación" />
            <x-text-input id="document_number" name="document_number" type="text" class="mt-1 block w-full" :value="old('document_number', $user->document_number)" />
            <x-input-error class="mt-2" :messages="$errors->get('document_number')" />
        </div>

        @if($user->isCustomer())
            <div>
                <x-input-label for="address_line1" value="Dirección (calle y número)" />
                <x-text-input id="address_line1" name="address_line1" type="text" class="mt-1 block w-full" :value="old('address_line1', $user->address_line1)" autocomplete="street-address" />
                <x-input-error class="mt-2" :messages="$errors->get('address_line1')" />
            </div>
            <div>
                <x-input-label for="address_line2" value="Edificio / apto / referencia interna (opcional)" />
                <x-text-input id="address_line2" name="address_line2" type="text" class="mt-1 block w-full" :value="old('address_line2', $user->address_line2)" />
                <x-input-error class="mt-2" :messages="$errors->get('address_line2')" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="city" value="Ciudad / municipio" />
                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>
                <div>
                    <x-input-label for="state" value="Provincia / estado" />
                    <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" autocomplete="address-level1" />
                    <x-input-error class="mt-2" :messages="$errors->get('state')" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="postal_code" value="Código postal (opcional)" />
                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $user->postal_code)" autocomplete="postal-code" />
                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                </div>
                <div>
                    <x-input-label for="country" value="País (ISO 2 letras)" />
                    <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" maxlength="2" :value="old('country', $user->country ?? 'DO')" autocomplete="country" />
                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                </div>
            </div>
            <div>
                <x-input-label for="delivery_instructions" value="Indicaciones para el domiciliario" />
                <textarea id="delivery_instructions" name="delivery_instructions" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--bf-red)] focus:ring-[var(--bf-red)]">{{ old('delivery_instructions', $user->delivery_instructions) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('delivery_instructions')" />
            </div>
        @endif
    </div>
</section>
