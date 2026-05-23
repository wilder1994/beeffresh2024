@php
    use App\Domain\Users\RoleSlug;
@endphp

<div>
    @include('livewire.admin.partials.user-form-header')

    <form wire:submit="save" class="space-y-4">
    <nav class="flex flex-wrap gap-1 border-b border-[var(--bf-border-brand-subtle)] -mb-px" role="tablist">
        <button type="button" wire:click="setActiveTab('cuenta')" @class(['px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 rounded-t-md', 'border-[var(--bf-brand)] text-[var(--bf-brand)]' => $activeTab === 'cuenta', 'border-transparent text-stone-600' => $activeTab !== 'cuenta'])>Cuenta</button>
        @if($role_slug === RoleSlug::EMPLOYEE)
            <button type="button" wire:click="setActiveTab('empleado')" @class(['px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 rounded-t-md', 'border-[var(--bf-brand)] text-[var(--bf-brand)]' => $activeTab === 'empleado', 'border-transparent text-stone-600' => $activeTab !== 'empleado'])>Empleado</button>
        @endif
        @if($role_slug === RoleSlug::CUSTOMER)
            <button type="button" wire:click="setActiveTab('cliente')" @class(['px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 rounded-t-md', 'border-[var(--bf-brand)] text-[var(--bf-brand)]' => $activeTab === 'cliente', 'border-transparent text-stone-600' => $activeTab !== 'cliente'])>Cliente</button>
        @endif
        @if($role_slug === RoleSlug::SUPPLIER)
            <button type="button" wire:click="setActiveTab('proveedor')" @class(['px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 rounded-t-md', 'border-[var(--bf-brand)] text-[var(--bf-brand)]' => $activeTab === 'proveedor', 'border-transparent text-stone-600' => $activeTab !== 'proveedor'])>Proveedor</button>
        @endif
    </nav>

    @if($activeTab === 'cuenta')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
        <div>
            <label class="bf-label" for="uf-first">Nombre</label>
            <input id="uf-first" type="text" wire:model.blur="first_name" class="bf-input @error('first_name') ring-1 ring-red-400 @enderror" />
            @error('first_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="uf-last">Apellido</label>
            <input id="uf-last" type="text" wire:model.blur="last_name" class="bf-input @error('last_name') ring-1 ring-red-400 @enderror" />
            @error('last_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="uf-email">Correo</label>
            <input id="uf-email" type="email" wire:model.blur="email" class="bf-input @error('email') ring-1 ring-red-400 @enderror" />
            @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="uf-phone">Teléfono</label>
            <input id="uf-phone" type="text" wire:model.blur="phone" class="bf-input" />
        </div>
        <div>
            <label class="bf-label" for="uf-doc-type">Tipo documento</label>
            <x-forms.document-type-select
                id="uf-doc-type"
                wire:model.blur="document_type"
                :wire-legacy-value="$document_type"
                class="@error('document_type') ring-1 ring-red-400 @enderror"
            />
            @error('document_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="uf-doc">Número documento</label>
            <input id="uf-doc" type="text" wire:model.blur="document_number" class="bf-input" />
        </div>
        <div>
            <label class="bf-label" for="uf-pass">Contraseña</label>
            <input id="uf-pass" type="password" wire:model.blur="password" class="bf-input" autocomplete="new-password" placeholder="{{ $userId ? 'Dejar vacío para no cambiar' : '' }}" />
            @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="bf-label" for="uf-pass2">Confirmar contraseña</label>
            <input id="uf-pass2" type="password" wire:model.blur="password_confirmation" class="bf-input" autocomplete="new-password" />
        </div>
        <div>
            <label class="bf-label" for="uf-status">Estado cuenta</label>
            <select id="uf-status" wire:model.live="status" class="bf-select">
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
            </select>
        </div>
        <div>
            <label class="bf-label" for="uf-role">Rol</label>
            <select id="uf-role" wire:model.live="role_slug" class="bf-select @error('role_slug') ring-1 ring-red-400 @enderror">
                @foreach($roleOptions as $r)
                    <option value="{{ $r }}">{{ \App\Domain\Users\RoleSlug::label($r) }}</option>
                @endforeach
            </select>
            @error('role_slug')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    @endif

    @if($role_slug === \App\Domain\Users\RoleSlug::EMPLOYEE && $activeTab === 'empleado')
        <div class="bf-form-section space-y-4">
            <p class="bf-form-section-title border-0 pb-0 mb-0">Empleado</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-pos">Cargo</label>
                    <select id="uf-pos" wire:model.live="employee_position_id" class="bf-select @error('employee_position_id') ring-1 ring-red-400 @enderror">
                        <option value="">— Seleccionar —</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                        @endforeach
                    </select>
                    @error('employee_position_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="bf-label" for="uf-hire">Fecha ingreso</label>
                    <input id="uf-hire" type="date" wire:model.blur="employee_hire_date" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-sal">Salario</label>
                    <input id="uf-sal" type="number" step="0.01" wire:model.blur="employee_salary" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-eps">EPS</label>
                    <input id="uf-eps" type="text" wire:model.blur="employee_eps" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-arl">ARL</label>
                    <input id="uf-arl" type="text" wire:model.blur="employee_arl" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-ec">Contacto emergencia</label>
                    <input id="uf-ec" type="text" wire:model.blur="employee_emergency_contact" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-ep">Teléfono emergencia</label>
                    <input id="uf-ep" type="text" wire:model.blur="employee_emergency_phone" class="bf-input" />
                </div>
                <x-forms.colombia-address
                    prefix="employee_home"
                    id-prefix="uf"
                    livewire
                    :required="false"
                    :address="$employee_home_address"
                    :neighborhood="$employee_home_neighborhood"
                    :city="$employee_home_city"
                    :department="$employee_home_state"
                    :latitude="$employee_home_latitude"
                    :longitude="$employee_home_longitude"
                    class="md:col-span-2 !p-0"
                />
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-en">Observaciones</label>
                    <textarea id="uf-en" wire:model.blur="employee_notes" class="bf-textarea min-h-[4rem]"></textarea>
                </div>
            </div>

            @if($this->isDeliveryPosition())
                <div class="bf-form-section--nested space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-[var(--bf-brand)]">Logística · Domiciliario</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
                        <div>
                            <label class="bf-label" for="uf-vt">Tipo vehículo</label>
                            <input id="uf-vt" type="text" wire:model.blur="employee_vehicle_type" class="bf-input @error('employee_vehicle_type') ring-1 ring-red-400 @enderror" />
                            @error('employee_vehicle_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="bf-label" for="uf-pl">Placa</label>
                            <input id="uf-pl" type="text" wire:model.blur="employee_plate_number" class="bf-input @error('employee_plate_number') ring-1 ring-red-400 @enderror" />
                            @error('employee_plate_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="bf-label" for="uf-lic">Licencia</label>
                            <input id="uf-lic" type="text" wire:model.blur="employee_driver_license" class="bf-input @error('employee_driver_license') ring-1 ring-red-400 @enderror" />
                            @error('employee_driver_license')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="bf-label" for="uf-lex">Vencimiento licencia</label>
                            <input id="uf-lex" type="date" wire:model.blur="employee_license_expiration" class="bf-input @error('employee_license_expiration') ring-1 ring-red-400 @enderror" />
                            @error('employee_license_expiration')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center gap-2 md:col-span-2">
                            <input id="uf-av" type="checkbox" wire:model.live="employee_available" class="checkbox checkbox-sm checkbox-primary" />
                            <label for="uf-av" class="bf-label-muted normal-case cursor-pointer">Disponible para rutas</label>
                        </div>
                        <div>
                            <label class="bf-label" for="uf-zone">Zona asignada</label>
                            <input id="uf-zone" type="text" wire:model.blur="employee_assigned_zone" class="bf-input" />
                        </div>
                        <div>
                            <label class="bf-label" for="uf-rate">Calificación promedio (0–5)</label>
                            <input id="uf-rate" type="number" step="0.01" min="0" max="5" wire:model.blur="employee_average_rating" class="bf-input" />
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <p class="bf-form-section-title">Permisos de acceso (módulos)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                    @foreach(\App\Domain\Users\PermissionKey::employeeModuleKeys() as $key)
                        <label class="bf-form-check-item">
                            <input type="checkbox" wire:model="permissions" value="{{ $key }}" class="checkbox checkbox-sm checkbox-primary" />
                            <span>{{ \App\Domain\Users\PermissionKey::label($key) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permissions.*')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    @endif

    @if($role_slug === \App\Domain\Users\RoleSlug::CUSTOMER)
        <div @class(['bf-form-section', 'hidden' => $activeTab !== 'cliente'])>
            <p class="bf-form-section-title border-0 pb-0 mb-0">Cliente · entrega</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
                <div wire:key="customer-address-{{ $userId ?? 'new' }}">
                <x-forms.colombia-address
                    prefix="customer"
                    id-prefix="uf"
                    livewire
                    :address="$customer_address"
                    :neighborhood="$customer_neighborhood"
                    :city="$customer_city"
                    :department="$customer_state"
                    :latitude="$customer_latitude"
                    :longitude="$customer_longitude"
                    show-reference
                    show-postal
                    show-delivery-notes
                    class="md:col-span-2 !p-0"
                >
                    <x-slot:reference>
                        <div class="md:col-span-2">
                            <label class="bf-label" for="uf-ref">Referencia dirección</label>
                            <input id="uf-ref" type="text" wire:model.blur="customer_address_reference" class="bf-input" />
                        </div>
                    </x-slot:reference>
                    <x-slot:postal>
                        <div>
                            <label class="bf-label" for="uf-cp">Código postal</label>
                            <input id="uf-cp" type="text" wire:model.blur="customer_postal_code" class="bf-input" />
                        </div>
                    </x-slot:postal>
                    <x-slot:deliveryNotes>
                        <div class="md:col-span-2">
                            <label class="bf-label" for="uf-del">Observaciones entrega</label>
                            <textarea id="uf-del" wire:model.blur="customer_delivery_notes" class="bf-textarea min-h-[3.5rem]"></textarea>
                        </div>
                    </x-slot:deliveryNotes>
                </x-forms.colombia-address>
                </div>
                <div class="flex items-center gap-2">
                    <input id="uf-promo" type="checkbox" wire:model.live="customer_accepts_promotions" class="checkbox checkbox-sm checkbox-primary" />
                    <label for="uf-promo" class="bf-label-muted normal-case cursor-pointer">Acepta promociones</label>
                </div>
                <div>
                    <label class="bf-label" for="uf-pts">Puntos fidelidad</label>
                    <input id="uf-pts" type="number" min="0" wire:model.blur="customer_loyalty_points" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-bal">Saldo a favor</label>
                    <input id="uf-bal" type="number" step="0.01" min="0" wire:model.blur="customer_balance" class="bf-input" />
                </div>
            </div>
        </div>
    @endif

    @if($role_slug === \App\Domain\Users\RoleSlug::SUPPLIER)
        <div @class(['bf-form-section', 'hidden' => $activeTab !== 'proveedor'])>
            <p class="bf-form-section-title border-0 pb-0 mb-0">Proveedor</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
                <div>
                    <label class="bf-label" for="uf-co">Empresa</label>
                    <input id="uf-co" type="text" wire:model.blur="supplier_company_name" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-nit">NIT</label>
                    <input id="uf-nit" type="text" wire:model.blur="supplier_nit" class="bf-input @error('supplier_nit') ring-1 ring-red-400 @enderror" />
                    @error('supplier_nit')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="bf-label" for="uf-sc">Contacto</label>
                    <input id="uf-sc" type="text" wire:model.blur="supplier_contact_name" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-sp">Teléfono empresa</label>
                    <input id="uf-sp" type="text" wire:model.blur="supplier_business_phone" class="bf-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-se">Correo empresa</label>
                    <input id="uf-se" type="email" wire:model.blur="supplier_business_email" class="bf-input" />
                </div>
                <x-forms.colombia-address
                    prefix="supplier"
                    id-prefix="uf"
                    livewire
                    :required="false"
                    :address="$supplier_business_address"
                    :neighborhood="$supplier_neighborhood"
                    :city="$supplier_city"
                    :department="$supplier_state"
                    :latitude="$supplier_latitude"
                    :longitude="$supplier_longitude"
                    class="md:col-span-2 !p-0"
                />
                <div>
                    <label class="bf-label" for="uf-bk">Banco</label>
                    <input id="uf-bk" type="text" wire:model.blur="supplier_bank_name" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-at">Tipo cuenta</label>
                    <input id="uf-at" type="text" wire:model.blur="supplier_account_type" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-an">Número cuenta</label>
                    <input id="uf-an" type="text" wire:model.blur="supplier_account_number" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-cd">Días crédito</label>
                    <input id="uf-cd" type="number" min="0" wire:model.blur="supplier_credit_days" class="bf-input" />
                </div>
            </div>
        </div>
    @endif

    <div class="bf-form-actions">
        <button type="submit" class="bf-btn-primary" wire:loading.attr="disabled">Guardar</button>
        @if($embedded)
            <button type="button" class="bf-btn-ghost" wire:click="$dispatch('user-form-cancelled')">Cancelar</button>
        @else
            <a href="{{ $userId ? route('admin.users.show', $userId) : route('admin.users.index') }}" class="bf-btn-ghost" wire:navigate>Cancelar</a>
        @endif
    </div>
</form>
</div>
