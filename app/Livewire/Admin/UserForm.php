<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use App\Models\Position;
use App\Models\User;
use App\Services\Admin\AdminUserPersistence;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    use WithFileUploads;

    public ?int $userId = null;

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

    public string $customer_country = 'DO';

    public ?string $supplier_company_name = null;

    public ?string $supplier_nit = null;

    public ?string $supplier_contact_name = null;

    public ?string $supplier_business_phone = null;

    public ?string $supplier_business_email = null;

    public ?string $supplier_business_address = null;

    public ?string $supplier_city = null;

    public ?string $supplier_bank_name = null;

    public ?string $supplier_account_type = null;

    public ?string $supplier_account_number = null;

    public ?int $supplier_credit_days = null;

    public $avatar = null;

    public ?string $existing_avatar_url = null;

    public function mount(?int $userId = null): void
    {
        $this->userId = $userId;
        if ($userId !== null) {
            $user = User::query()
                ->with(['roles', 'employeeProfile.position', 'customerProfile', 'supplierProfile'])
                ->findOrFail($userId);
            $this->hydrateFromUser($user);
        }
    }

    public function updatedRoleSlug(string $value): void
    {
        if ($value !== RoleSlug::EMPLOYEE) {
            $this->permissions = [];
            $this->employee_position_id = null;
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
        $this->validate($this->rules(), $this->messages());

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
            'supplier_company_name' => $this->supplier_company_name,
            'supplier_nit' => $this->supplier_nit,
            'supplier_contact_name' => $this->supplier_contact_name,
            'supplier_business_phone' => $this->supplier_business_phone,
            'supplier_business_email' => $this->supplier_business_email,
            'supplier_business_address' => $this->supplier_business_address,
            'supplier_city' => $this->supplier_city,
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
            'document_type' => ['nullable', 'string', 'max:32'],
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
            $rules['customer_neighborhood'] = ['nullable', 'string', 'max:120'];
            $rules['customer_city'] = ['required', 'string', 'max:120'];
            $rules['customer_state'] = ['required', 'string', 'max:120'];
            $rules['customer_address_reference'] = ['nullable', 'string', 'max:255'];
            $rules['customer_delivery_notes'] = ['nullable', 'string', 'max:2000'];
            $rules['customer_accepts_promotions'] = ['boolean'];
            $rules['customer_loyalty_points'] = ['integer', 'min:0'];
            $rules['customer_balance'] = ['numeric', 'min:0'];
            $rules['customer_postal_code'] = ['nullable', 'string', 'max:32'];
            $rules['customer_country'] = ['nullable', 'string', 'size:2'];
        }

        if ($this->role_slug === RoleSlug::SUPPLIER) {
            $rules['supplier_company_name'] = ['nullable', 'string', 'max:191'];
            $rules['supplier_nit'] = ['required', 'string', 'max:64'];
            $rules['supplier_contact_name'] = ['nullable', 'string', 'max:191'];
            $rules['supplier_business_phone'] = ['nullable', 'string', 'max:32'];
            $rules['supplier_business_email'] = ['nullable', 'email', 'max:191'];
            $rules['supplier_business_address'] = ['nullable', 'string', 'max:255'];
            $rules['supplier_city'] = ['nullable', 'string', 'max:120'];
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
            $this->customer_country = $cp->country ?? 'DO';
        }

        $sp = $user->supplierProfile;
        if ($sp) {
            $this->supplier_company_name = $sp->company_name;
            $this->supplier_nit = $sp->nit;
            $this->supplier_contact_name = $sp->contact_name;
            $this->supplier_business_phone = $sp->business_phone;
            $this->supplier_business_email = $sp->business_email;
            $this->supplier_business_address = $sp->business_address;
            $this->supplier_city = $sp->city;
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
