@php
    use App\Domain\Users\RoleSlug;
    use App\Domain\Users\PermissionKey;
    $roleSlug = $user->primaryRoleSlug();
@endphp

<div x-show="tab === 'cuenta'" x-cloak class="space-y-4" role="tabpanel">
    <dl class="bf-account-grid">
        <x-account.field label="Nombre" :value="$user->name" />
        <x-account.field label="Correo" :value="$user->email" />
        <x-account.field label="Teléfono" :value="$user->phone" />
        <x-account.field label="Tipo documento" :value="$user->document_type" />
        <x-account.field label="Número documento" :value="$user->document_number" />
        <x-account.field label="Rol" :value="$roleSlug ? RoleSlug::label($roleSlug) : null" />
        <x-account.field label="Audiencia" :value="$roleSlug ? RoleSlug::audienceLabel($roleSlug) : null" />
        <x-account.field label="Estado" :value="$user->status === 'active' ? 'Activo' : 'Inactivo'" />
    </dl>
</div>

@if($user->isEmployee() && $user->employeeProfile)
    @php($ep = $user->employeeProfile)
    <div x-show="tab === 'empleado'" x-cloak class="space-y-4" role="tabpanel">
        <dl class="bf-account-grid">
            <x-account.field label="Cargo" :value="$ep->position?->name" />
            <x-account.field label="Fecha ingreso" :value="$ep->hire_date?->format('d/m/Y')" />
            <x-account.field label="Salario" :value="$ep->salary !== null ? number_format((float) $ep->salary, 2) : null" />
            <x-account.field label="EPS" :value="$ep->eps" />
            <x-account.field label="ARL" :value="$ep->arl" />
            <x-account.field label="Contacto emergencia" :value="$ep->emergency_contact" />
            <x-account.field label="Tel. emergencia" :value="$ep->emergency_phone" />
            <x-account.field label="Dirección" :value="$ep->home_address" :colspan="true" />
            @if($ep->isDeliveryRole())
                <x-account.field label="Vehículo" :value="$ep->vehicle_type" />
                <x-account.field label="Placa" :value="$ep->plate_number" />
                <x-account.field label="Licencia" :value="$ep->driver_license" />
                <x-account.field label="Vence licencia" :value="$ep->license_expiration?->format('d/m/Y')" />
                <x-account.field label="Zona" :value="$ep->assigned_zone" />
                <x-account.field label="Disponible" :value="$ep->available ? 'Sí' : 'No'" />
            @endif
            <x-account.field label="Observaciones" :value="$ep->notes" :colspan="true" />
        </dl>
        @if($user->permissions->isNotEmpty())
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500 mb-2">Permisos</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($user->getPermissionNames() as $key)
                        <span class="badge badge-sm border border-stone-200 bg-stone-50 text-stone-700">{{ PermissionKey::label($key) }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif

@if($user->isCustomer() && $user->customerProfile)
    @php($cp = $user->customerProfile)
    <div x-show="tab === 'cliente'" x-cloak class="space-y-4" role="tabpanel">
        <dl class="bf-account-grid">
            <x-account.field label="Dirección" :value="$cp->address" :colspan="true" />
            <x-account.field label="Barrio" :value="$cp->neighborhood" />
            <x-account.field label="Ciudad" :value="$cp->city" />
            <x-account.field label="Provincia" :value="$cp->state" />
            <x-account.field label="Código postal" :value="$cp->postal_code" />
            <x-account.field label="País" :value="$cp->country" />
            <x-account.field label="Referencia" :value="$cp->address_reference" :colspan="true" />
            <x-account.field label="Indicaciones entrega" :value="$cp->delivery_notes" :colspan="true" />
            <x-account.field label="Promociones" :value="$cp->accepts_promotions ? 'Sí' : 'No'" />
            <x-account.field label="Puntos" :value="(string) $cp->loyalty_points" />
            <x-account.field label="Saldo" :value="number_format((float) $cp->balance, 2)" />
        </dl>
    </div>
@endif

@if($user->isSupplier() && $user->supplierProfile)
    @php($sp = $user->supplierProfile)
    <div x-show="tab === 'proveedor'" x-cloak class="space-y-4" role="tabpanel">
        <dl class="bf-account-grid">
            <x-account.field label="Empresa" :value="$sp->company_name" />
            <x-account.field label="NIT" :value="$sp->nit" />
            <x-account.field label="Contacto" :value="$sp->contact_name" />
            <x-account.field label="Teléfono" :value="$sp->business_phone" />
            <x-account.field label="Correo" :value="$sp->business_email" />
            <x-account.field label="Dirección" :value="$sp->business_address" :colspan="true" />
            <x-account.field label="Ciudad" :value="$sp->city" />
            <x-account.field label="Banco" :value="$sp->bank_name" />
            <x-account.field label="Tipo cuenta" :value="$sp->account_type" />
            <x-account.field label="Número cuenta" :value="$sp->account_number" />
            <x-account.field label="Días crédito" :value="$sp->credit_days !== null ? (string) $sp->credit_days : null" />
        </dl>
    </div>
@endif
