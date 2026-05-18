@php
    use App\Domain\Users\RoleSlug;
@endphp

<form wire:submit="save" class="space-y-4">
    <nav class="flex flex-wrap gap-1 border-b border-stone-200 -mb-px" role="tablist">
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
    <div class="flex flex-col items-center gap-2 py-1">
        <div class="relative shrink-0">
            <div class="h-24 w-24 rounded-full overflow-hidden ring-2 ring-stone-200 bg-stone-100 flex items-center justify-center">
                @if($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="" class="h-full w-full object-cover" />
                @elseif($existing_avatar_url)
                    <img src="{{ $existing_avatar_url }}" alt="" class="h-full w-full object-cover" />
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                @endif
            </div>
            <label for="uf-avatar" class="btn btn-circle btn-xs bg-white text-[var(--bf-rust-deep)] border-0 shadow-md absolute -bottom-0.5 -right-0.5 cursor-pointer" title="Foto">
                <span class="sr-only">Elegir foto</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </label>
            <input id="uf-avatar" type="file" wire:model="avatar" class="sr-only" accept="image/*" />
        </div>
        @error('avatar')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

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
            <input id="uf-doc-type" type="text" wire:model.blur="document_type" class="bf-input" placeholder="Cédula, RNC…" />
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
        <div class="rounded-xl border border-stone-200 bg-stone-50/80 p-4 space-y-4">
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
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-home">Dirección residencia</label>
                    <input id="uf-home" type="text" wire:model.blur="employee_home_address" class="bf-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-en">Observaciones</label>
                    <textarea id="uf-en" wire:model.blur="employee_notes" class="bf-textarea min-h-[4rem]"></textarea>
                </div>
            </div>

            @if($this->isDeliveryPosition())
                <div class="rounded-lg border border-amber-200 bg-amber-50/60 p-4 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-amber-900">Logística · Domiciliario</p>
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
                        <label class="flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm cursor-pointer hover:border-stone-300">
                            <input type="checkbox" wire:model="permissions" value="{{ $key }}" class="checkbox checkbox-sm checkbox-primary" />
                            <span>{{ \App\Domain\Users\PermissionKey::label($key) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permissions.*')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    @endif

    @if($role_slug === \App\Domain\Users\RoleSlug::CUSTOMER && $activeTab === 'cliente')
        <div class="rounded-xl border border-stone-200 bg-white p-4 space-y-3">
            <p class="bf-form-section-title border-0 pb-0 mb-0">Cliente · entrega</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-addr">Dirección</label>
                    <input id="uf-addr" type="text" wire:model.blur="customer_address" class="bf-input @error('customer_address') ring-1 ring-red-400 @enderror" />
                    @error('customer_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="bf-label" for="uf-bar">Barrio</label>
                    <input id="uf-bar" type="text" wire:model.blur="customer_neighborhood" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-ci">Ciudad</label>
                    <input id="uf-ci" type="text" wire:model.blur="customer_city" class="bf-input @error('customer_city') ring-1 ring-red-400 @enderror" />
                    @error('customer_city')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="bf-label" for="uf-st">Provincia</label>
                    <input id="uf-st" type="text" wire:model.blur="customer_state" class="bf-input @error('customer_state') ring-1 ring-red-400 @enderror" />
                    @error('customer_state')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-ref">Referencia dirección</label>
                    <input id="uf-ref" type="text" wire:model.blur="customer_address_reference" class="bf-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-del">Observaciones entrega</label>
                    <textarea id="uf-del" wire:model.blur="customer_delivery_notes" class="bf-textarea min-h-[3.5rem]"></textarea>
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
                <div>
                    <label class="bf-label" for="uf-cp">Código postal</label>
                    <input id="uf-cp" type="text" wire:model.blur="customer_postal_code" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-ctry">País (ISO-2)</label>
                    <input id="uf-ctry" type="text" maxlength="2" wire:model.blur="customer_country" class="bf-input" />
                </div>
            </div>
        </div>
    @endif

    @if($role_slug === \App\Domain\Users\RoleSlug::SUPPLIER && $activeTab === 'proveedor')
        <div class="rounded-xl border border-stone-200 bg-white p-4 space-y-3">
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
                <div class="md:col-span-2">
                    <label class="bf-label" for="uf-sa">Dirección empresa</label>
                    <input id="uf-sa" type="text" wire:model.blur="supplier_business_address" class="bf-input" />
                </div>
                <div>
                    <label class="bf-label" for="uf-sci">Ciudad</label>
                    <input id="uf-sci" type="text" wire:model.blur="supplier_city" class="bf-input" />
                </div>
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
