<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;

    use Notifiable;

    protected $guard_name = 'web';

    protected $fillable = [
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        });
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function supplierProfile(): HasOne
    {
        return $this->hasOne(SupplierProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    public function courierLocations(): HasMany
    {
        return $this->hasMany(CourierLocation::class)->latest('recorded_at');
    }

    public function courierAssignments(): HasMany
    {
        return $this->hasMany(OrderAssignment::class, 'courier_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleSlug::ADMIN);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(RoleSlug::CUSTOMER);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole([RoleSlug::ADMIN, RoleSlug::EMPLOYEE]);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(RoleSlug::EMPLOYEE);
    }

    public function isSupplier(): bool
    {
        return $this->hasRole(RoleSlug::SUPPLIER);
    }

    public function isCourier(): bool
    {
        return $this->isEmployee()
            && ($this->employeeProfile?->isDeliveryRole() ?? false);
    }

    public function isDispatcher(): bool
    {
        return $this->isEmployee()
            && ($this->employeeProfile?->isDispatchRole() ?? false);
    }

    public function canAccessCourierModule(): bool
    {
        return $this->isCourier() && $this->can(PermissionKey::MODULE_COURIER);
    }

    public function canAccessOrderOperations(): bool
    {
        return $this->can(PermissionKey::MODULE_ORDERS);
    }

    /** Primer rol Spatie (una sola asignación prevista en UI). */
    public function primaryRoleSlug(): ?string
    {
        $role = $this->roles->first();

        return $role?->name;
    }

    /** Ciudad mostrada en listados admin (cliente / proveedor). */
    public function primaryCityForList(): ?string
    {
        return $this->customerProfile?->city
            ?? $this->supplierProfile?->city;
    }

    public function adminUsersListRoute(): string
    {
        $slug = $this->primaryRoleSlug();

        return match (RoleSlug::audienceId($slug ?? '')) {
            'clients' => route('admin.users.clientes'),
            'suppliers' => route('admin.users.proveedores'),
            'company' => route('admin.users.empresa'),
            default => route('admin.users.index'),
        };
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar === null || $this->avatar === '') {
            return null;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function hasCompleteDeliveryProfile(): bool
    {
        if (! $this->isCustomer()) {
            return true;
        }

        return $this->filledDeliveryBasics();
    }

    public function filledDeliveryBasics(): bool
    {
        $p = $this->customerProfile;
        if ($p === null) {
            return false;
        }

        return $this->phone !== null && trim((string) $this->phone) !== ''
            && $p->address !== null && trim((string) $p->address) !== ''
            && $p->city !== null && trim((string) $p->city) !== ''
            && $p->state !== null && trim((string) $p->state) !== ''
            && $p->neighborhood !== null && trim((string) $p->neighborhood) !== '';
    }

    /**
     * @return array<string, string|null>
     */
    public function snapshotShippingFromProfile(): array
    {
        $p = $this->customerProfile;

        return [
            'shipping_recipient_name' => $this->name,
            'shipping_phone' => $this->phone,
            'shipping_document_number' => $this->document_number,
            'shipping_address_line1' => $p?->address,
            'shipping_address_line2' => $p?->neighborhood,
            'shipping_city' => $p?->city,
            'shipping_state' => $p?->state,
            'shipping_postal_code' => $p?->postal_code,
            'shipping_country' => $p?->country ?? 'CO',
            'shipping_notes' => $p?->delivery_notes,
            'shipping_latitude' => $p?->latitude,
            'shipping_longitude' => $p?->longitude,
        ];
    }
}
