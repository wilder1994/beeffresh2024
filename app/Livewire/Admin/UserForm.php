<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Geo\Colombia;
use App\Domain\Users\ColombianDocumentType;
use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use App\Models\Position;
use App\Models\User;
use App\Services\Admin\AdminUserPersistence;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    use WithFileUploads;

    public ?int $userId = null;

    public bool $embedded = false;

    public string $activeTab = 'cuenta';

    public string $first_name = '';

    public string $last_name = '';

    public ?string $document_type = null;

    public ?string $document_number = null;

    public ?string $phone = null;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $status = 'active';

    public string $role_slug = RoleSlug::CUSTOMER;

    /** @var list<string> */
    public array $permissions = [];

    public ?int $employee_position_id = null;

    public ?string $employee_hire_date = null;

    public ?string $employee_salary = null;

    public ?string $employee_eps = null;

    public ?string $employee_arl = null;

    public ?string $employee_emergency_contact = null;

    public ?string $employee_emergency_phone = null;

    public ?string $employee_home_address = null;

    public ?string $employee_home_neighborhood = null;

    public ?string $employee_home_city = null;

    public ?string $employee_home_state = null;

    public string $employee_home_country = Colombia::COUNTRY_CODE;

    public ?string $employee_home_latitude = null;

    public ?string $employee_home_longitude = null;

    public ?string $employee_notes = null;

    public ?string $employee_vehicle_type = null;

    public ?string $employee_plate_number = null;

    public ?string $employee_driver_license = null;

    public ?string $employee_license_expiration = null;

    public bool $employee_available = true;

    public ?string $employee_assigned_zone = null;

    public ?string $employee_average_rating = null;

    public ?string $customer_address = null;

    public ?string $customer_neighborhood = null;

    public ?string $customer_city = null;

    public ?string $customer_state = null;

    public ?string $customer_address_reference = null;

    public ?string $customer_delivery_notes = null;

    public bool $customer_accepts_promotions = true;

    public int $customer_loyalty_points = 0;

    public string $customer_balance = '0';

    public ?string $customer_postal_code = null;

    public string $customer_country = Colombia::COUNTRY_CODE;

    public ?string $customer_latitude = null;

    public ?string $customer_longitude = null;

    public ?string $supplier_company_name = null;

    public ?string $supplier_nit = null;

    public ?string $supplier_contact_name = null;

    public ?string $supplier_business_phone = null;

    public ?string $supplier_business_email = null;

    public ?string $supplier_business_address = null;

    public ?string $supplier_neighborhood = null;

    public ?string $supplier_city = null;

    public ?string $supplier_state = null;

    public string $supplier_country = Colombia::COUNTRY_CODE;

    public ?string $supplier_latitude = null;

    public ?string $supplier_longitude = null;

    public ?string $supplier_bank_name = null;

    public ?string $supplier_account_type = null;

    public ?string $supplier_account_number = null;

    public ?int $supplier_credit_days = null;

    public $avatar = null;

    public ?string $existing_avatar_url = null;

    public function mount(?int $userId = null, bool $embedded = false): void
    {
        $this->userId = $userId;
        $this->embedded = $embedded;
        if ($userId !== null) {
            $user = User::query()
                ->with(['roles', 'employeeProfile.position', 'customerProfile', 'supplierProfile'])
                ->findOrFail($userId);
            $this->hydrateFromUser($user);
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function closeEmbedded(): void
    {
        $this->dispatch('close-user-account');
    }

    public function showEmbeddedView(): void
    {
        $this->dispatch('user-account-show-view');
    }

    private function focusTabForValidationErrors(ValidationException $e): void
    {
        $keys = array_keys($e->validator->errors()->messages());
        $employeePrefixes = ['employee_', 'permissions'];
        $customerPrefixes = ['customer_'];
        $supplierPrefixes = ['supplier_'];

        foreach ($keys as $key) {
            foreach ($employeePrefixes as $prefix) {
                if (str_starts_with($key, $prefix) && $this->role_slug === RoleSlug::EMPLOYEE) {
                    $this->activeTab = 'empleado';

                    return;
                }
            }
            foreach ($customerPrefixes as $prefix) {
                if (str_starts_with($key, $prefix) && $this->role_slug === RoleSlug::CUSTOMER) {
                    $this->activeTab = 'cliente';

                    return;
                }
            }
            foreach ($supplierPrefixes as $prefix) {
                if (str_starts_with($key, $prefix) && $this->role_slug === RoleSlug::SUPPLIER) {
                    $this->activeTab = 'proveedor';

                    return;
                }
            }
        }
    }

    public function updatedRoleSlug(string $value): void
    {
        if ($value !== RoleSlug::EMPLOYEE) {
            $this->permissions = [];
            $this->employee_position_id = null;
            if ($this->activeTab === 'empleado') {
                $this->activeTab = 'cuenta';
            }
        }
        if ($value !== RoleSlug::CUSTOMER && $this->activeTab === 'cliente') {
            $this->activeTab = 'cuenta';
        }
        if ($value !== RoleSlug::SUPPLIER && $this->activeTab === 'proveedor') {
            $this->activeTab = 'cuenta';
        }
    }

    public function updatedEmployeePositionId(): void
    {
        if (! $this->isDeliveryPosition()) {
            $this->employee_vehicle_type = null;
            $this->employee_plate_number = null;
            $this->employee_driver_license = null;
            $this->employee_license_expiration = null;
            $this->employee_assigned_zone = null;
            $this->employee_average_rating = null;
        }
    }

    public function isDeliveryPosition(): bool
    {
        if ($this->employee_position_id === null) {
            return false;
        }
        $p = Position::query()->find($this->employee_position_id);

        return $p?->isDelivery() ?? false;
    }

    public function save(AdminUserPersistence $persistence): void
    {
        try {
            $this->validate($this->rules(), $this->messages());
        } catch (ValidationException $e) {
            $this->focusTabForValidationErrors($e);
            throw $e;
        }

        if ($this->userId !== null) {
            $existing = User::query()->findOrFail($this->userId);
            if ($existing->hasRole(RoleSlug::ADMIN) && $this->role_slug !== RoleSlug::ADMIN) {
                $others = User::query()->role(RoleSlug::ADMIN)->where('id', '!=', $existing->id)->count();
                if ($others < 1) {
                    $this->addError('role_slug', 'Debe existir al menos otro administrador antes de cambiar este rol.');

                    return;
                }
            }
        }

        $payload = $this->payloadArray();
        $avatarFile = $this->avatar;
        $this->avatar = null;

        $user = $persistence->persist(
            $payload,
            $this->userId ? User::query()->findOrFail($this->userId) : null,
            $avatarFile
        );

        session()->flash('success', $this->userId ? 'Usuario actualizado.' : 'Usuario creado correctamente.');

        if ($this->embedded) {
            $this->userId = $user->id;
            $this->hydrateFromUser($user->fresh(['roles', 'employeeProfile.position', 'customerProfile', 'supplierProfile']));
            $this->dispatch('user-saved', userId: $user->id);

            return;
        }

        $this->redirect(route('admin.users.show', $user));
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadArray(): array
    {
        return [
            'role_slug' => $this->role_slug,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'phone' => $this->phone,
            'email' => $this->email,
            'password' => $this->password,
            'status' => $this->status,
            'permissions' => $this->permissions,
            'employee_position_id' => $this->employee_position_id,
            'employee_hire_date' => $this->employee_hire_date,
            'employee_salary' => $this->employee_salary,
            'employee_eps' => $this->employee_eps,
            'employee_arl' => $this->employee_arl,
            'employee_emergency_contact' => $this->employee_emergency_contact,
            'employee_emergency_phone' => $this->employee_emergency_phone,
            'employee_home_address' => $this->employee_home_address,
            'employee_home_neighborhood' => $this->employee_home_neighborhood,
            'employee_home_city' => $this->employee_home_city,
            'employee_home_state' => $this->employee_home_state,
            'employee_home_country' => $this->employee_home_country,
            'employee_home_latitude' => $this->employee_home_latitude,
            'employee_home_longitude' => $this->employee_home_longitude,
            'employee_notes' => $this->employee_notes,
            'employee_vehicle_type' => $this->employee_vehicle_type,
            'employee_plate_number' => $this->employee_plate_number,
            'employee_driver_license' => $this->employee_driver_license,
            'employee_license_expiration' => $this->employee_license_expiration,
            'employee_available' => $this->employee_available,
            'employee_assigned_zone' => $this->employee_assigned_zone,
            'employee_average_rating' => $this->employee_average_rating,
            'customer_address' => $this->customer_address,
            'customer_neighborhood' => $this->customer_neighborhood,
            'customer_city' => $this->customer_city,
            'customer_state' => $this->customer_state,
            'customer_address_reference' => $this->customer_address_reference,
            'customer_delivery_notes' => $this->customer_delivery_notes,
            'customer_accepts_promotions' => $this->customer_accepts_promotions,
            'customer_loyalty_points' => $this->customer_loyalty_points,
            'customer_balance' => $this->customer_balance,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_country' => $this->customer_country,
            'customer_latitude' => $this->customer_latitude,
            'customer_longitude' => $this->customer_longitude,
            'supplier_company_name' => $this->supplier_company_name,
            'supplier_nit' => $this->supplier_nit,
            'supplier_contact_name' => $this->supplier_contact_name,
            'supplier_business_phone' => $this->supplier_business_phone,
            'supplier_business_email' => $this->supplier_business_email,
            'supplier_business_address' => $this->supplier_business_address,
            'supplier_neighborhood' => $this->supplier_neighborhood,
            'supplier_city' => $this->supplier_city,
            'supplier_state' => $this->supplier_state,
            'supplier_country' => $this->supplier_country,
            'supplier_latitude' => $this->supplier_latitude,
            'supplier_longitude' => $this->supplier_longitude,
            'supplier_bank_name' => $this->supplier_bank_name,
            'supplier_account_type' => $this->supplier_account_type,
            'supplier_account_number' => $this->supplier_account_number,
            'supplier_credit_days' => $this->supplier_credit_days,
        ];
    }

    /**
     * @return array<string, list<string|\Illuminate\Contracts\Validation\Rule>>
     */
    private function rules(): array
    {
        $userId = $this->userId;
        $rules = [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'document_type' => ColombianDocumentType::validationRules(),
            'document_number' => ['nullable', 'string', 'max:64'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $userId
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role_slug' => ['required', Rule::in(RoleSlug::all())],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        if ($this->role_slug === RoleSlug::EMPLOYEE) {
            $rules['employee_position_id'] = ['required', 'exists:positions,id'];
            $rules['employee_hire_date'] = ['nullable', 'date'];
            $rules['employee_salary'] = ['nullable', 'numeric', 'min:0'];
            $rules['employee_eps'] = ['nullable', 'string', 'max:191'];
            $rules['employee_arl'] = ['nullable', 'string', 'max:191'];
            $rules['employee_emergency_contact'] = ['nullable', 'string', 'max:191'];
            $rules['employee_emergency_phone'] = ['nullable', 'string', 'max:32'];
            $rules['employee_home_address'] = ['nullable', 'string', 'max:255'];
            $rules['employee_home_neighborhood'] = ['nullable', 'string', 'max:120'];
            $rules['employee_home_city'] = ['nullable', 'string', 'max:120'];
            $rules['employee_home_state'] = ['nullable', 'string', 'max:120'];
            $rules['employee_home_country'] = ['nullable', 'string', 'size:2', Rule::in([Colombia::COUNTRY_CODE])];
            $rules['employee_home_latitude'] = ['nullable', 'numeric', 'between:-90,90'];
            $rules['employee_home_longitude'] = ['nullable', 'numeric', 'between:-180,180'];
            $rules['employee_notes'] = ['nullable', 'string', 'max:5000'];
            $rules['permissions'] = ['array'];
            $rules['permissions.*'] = ['string', Rule::in(PermissionKey::employeeModuleKeys())];

            if ($this->isDeliveryPosition()) {
                $rules['employee_vehicle_type'] = ['required', 'string', 'max:64'];
                $rules['employee_plate_number'] = ['required', 'string', 'max:32'];
                $rules['employee_driver_license'] = ['required', 'string', 'max:64'];
                $rules['employee_license_expiration'] = ['required', 'date'];
                $rules['employee_available'] = ['boolean'];
                $rules['employee_assigned_zone'] = ['nullable', 'string', 'max:191'];
                $rules['employee_average_rating'] = ['nullable', 'numeric', 'between:0,5'];
            }
        }

        if ($this->role_slug === RoleSlug::CUSTOMER) {
            $rules['customer_address'] = ['required', 'string', 'max:255'];
            $rules['customer_neighborhood'] = ['required', 'string', 'max:120'];
            $rules['customer_city'] = ['required', 'string', 'max:120'];
            $rules['customer_state'] = ['required', 'string', 'max:120'];
            $rules['customer_address_reference'] = ['nullable', 'string', 'max:255'];
            $rules['customer_delivery_notes'] = ['nullable', 'string', 'max:2000'];
            $rules['customer_accepts_promotions'] = ['boolean'];
            $rules['customer_loyalty_points'] = ['integer', 'min:0'];
            $rules['customer_balance'] = ['numeric', 'min:0'];
            $rules['customer_postal_code'] = ['nullable', 'string', 'max:32'];
            $rules['customer_country'] = ['required', 'string', 'size:2', Rule::in([Colombia::COUNTRY_CODE])];
            $rules['customer_latitude'] = ['nullable', 'numeric', 'between:-90,90'];
            $rules['customer_longitude'] = ['nullable', 'numeric', 'between:-180,180'];
        }

        if ($this->role_slug === RoleSlug::SUPPLIER) {
            $rules['supplier_company_name'] = ['nullable', 'string', 'max:191'];
            $rules['supplier_nit'] = ['required', 'string', 'max:64'];
            $rules['supplier_contact_name'] = ['nullable', 'string', 'max:191'];
            $rules['supplier_business_phone'] = ['nullable', 'string', 'max:32'];
            $rules['supplier_business_email'] = ['nullable', 'email', 'max:191'];
            $rules['supplier_business_address'] = ['nullable', 'string', 'max:255'];
            $rules['supplier_neighborhood'] = ['nullable', 'string', 'max:120'];
            $rules['supplier_city'] = ['nullable', 'string', 'max:120'];
            $rules['supplier_state'] = ['nullable', 'string', 'max:120'];
            $rules['supplier_country'] = ['nullable', 'string', 'size:2', Rule::in([Colombia::COUNTRY_CODE])];
            $rules['supplier_latitude'] = ['nullable', 'numeric', 'between:-90,90'];
            $rules['supplier_longitude'] = ['nullable', 'numeric', 'between:-180,180'];
            $rules['supplier_bank_name'] = ['nullable', 'string', 'max:120'];
            $rules['supplier_account_type'] = ['nullable', 'string', 'max:64'];
            $rules['supplier_account_number'] = ['nullable', 'string', 'max:64'];
            $rules['supplier_credit_days'] = ['nullable', 'integer', 'min:0', 'max:365'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'employee_position_id.required' => 'Seleccione un cargo.',
            'supplier_nit.required' => 'El NIT / documento fiscal es obligatorio para proveedores.',
        ];
    }

    private function hydrateFromUser(User $user): void
    {
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->document_type = $user->document_type;
        $this->document_number = $user->document_number;
        $this->phone = $user->phone;
        $this->email = $user->email;
        $this->status = $user->status;
        $this->role_slug = $user->primaryRoleSlug() ?? RoleSlug::CUSTOMER;
        $this->permissions = $user->getPermissionNames()->all();

        $ep = $user->employeeProfile;
        if ($ep) {
            $this->employee_position_id = $ep->position_id;
            $this->employee_hire_date = $ep->hire_date?->format('Y-m-d');
            $this->employee_salary = $ep->salary !== null ? (string) $ep->salary : null;
            $this->employee_eps = $ep->eps;
            $this->employee_arl = $ep->arl;
            $this->employee_emergency_contact = $ep->emergency_contact;
            $this->employee_emergency_phone = $ep->emergency_phone;
            $this->employee_home_address = $ep->home_address;
            $this->employee_home_neighborhood = $ep->home_neighborhood;
            $this->employee_home_city = $ep->home_city;
            $this->employee_home_state = $ep->home_state;
            $this->employee_home_country = $ep->home_country ?? Colombia::COUNTRY_CODE;
            $this->employee_home_latitude = $ep->home_latitude !== null ? (string) $ep->home_latitude : null;
            $this->employee_home_longitude = $ep->home_longitude !== null ? (string) $ep->home_longitude : null;
            $this->employee_notes = $ep->notes;
            $this->employee_vehicle_type = $ep->vehicle_type;
            $this->employee_plate_number = $ep->plate_number;
            $this->employee_driver_license = $ep->driver_license;
            $this->employee_license_expiration = $ep->license_expiration?->format('Y-m-d');
            $this->employee_available = $ep->available;
            $this->employee_assigned_zone = $ep->assigned_zone;
            $this->employee_average_rating = $ep->average_rating !== null ? (string) $ep->average_rating : null;
        }

        $cp = $user->customerProfile;
        if ($cp) {
            $this->customer_address = $cp->address;
            $this->customer_neighborhood = $cp->neighborhood;
            $this->customer_city = $cp->city;
            $this->customer_state = $cp->state;
            $this->customer_address_reference = $cp->address_reference;
            $this->customer_delivery_notes = $cp->delivery_notes;
            $this->customer_accepts_promotions = $cp->accepts_promotions;
            $this->customer_loyalty_points = (int) $cp->loyalty_points;
            $this->customer_balance = (string) $cp->balance;
            $this->customer_postal_code = $cp->postal_code;
            $this->customer_country = $cp->country ?? Colombia::COUNTRY_CODE;
            $this->customer_latitude = $cp->latitude !== null ? (string) $cp->latitude : null;
            $this->customer_longitude = $cp->longitude !== null ? (string) $cp->longitude : null;
        }

        $sp = $user->supplierProfile;
        if ($sp) {
            $this->supplier_company_name = $sp->company_name;
            $this->supplier_nit = $sp->nit;
            $this->supplier_contact_name = $sp->contact_name;
            $this->supplier_business_phone = $sp->business_phone;
            $this->supplier_business_email = $sp->business_email;
            $this->supplier_business_address = $sp->business_address;
            $this->supplier_neighborhood = $sp->neighborhood;
            $this->supplier_city = $sp->city;
            $this->supplier_state = $sp->state;
            $this->supplier_country = $sp->country ?? Colombia::COUNTRY_CODE;
            $this->supplier_latitude = $sp->latitude !== null ? (string) $sp->latitude : null;
            $this->supplier_longitude = $sp->longitude !== null ? (string) $sp->longitude : null;
            $this->supplier_bank_name = $sp->bank_name;
            $this->supplier_account_type = $sp->account_type;
            $this->supplier_account_number = $sp->account_number;
            $this->supplier_credit_days = $sp->credit_days;
        }

        $this->existing_avatar_url = $user->avatarUrl();
    }

    public function render(): View
    {
        return view('livewire.admin.user-form', [
            'positions' => Position::query()->where('status', 'active')->orderBy('name')->get(),
            'roleOptions' => RoleSlug::all(),
        ]);
    }
}
