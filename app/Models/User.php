<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'document_number',
        'company_name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'delivery_instructions',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    public function isSupplier(): bool
    {
        return $this->role->isSupplier();
    }

    /** Listado admin acorde al público del usuario (clientes, empresa o proveedores). */
    public function adminUsersListRoute(): string
    {
        return match ($this->role->audienceId()) {
            'clients' => route('admin.users.clientes'),
            'company' => route('admin.users.empresa'),
            'suppliers' => route('admin.users.proveedores'),
            default => route('admin.users.index'),
        };
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar_path === null || $this->avatar_path === '') {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar_path);
    }

    /** Datos mínimos para despacho a domicilio (solo aplica a compradores). */
    public function hasCompleteDeliveryProfile(): bool
    {
        if (! $this->isCustomer()) {
            return true;
        }

        return $this->filledDeliveryBasics();
    }

    public function filledDeliveryBasics(): bool
    {
        return $this->phone !== null && trim((string) $this->phone) !== ''
            && $this->address_line1 !== null && trim((string) $this->address_line1) !== ''
            && $this->city !== null && trim((string) $this->city) !== ''
            && $this->state !== null && trim((string) $this->state) !== '';
    }

    /**
     * Copia al crear el pedido para conservar domicilio aunque el cliente cambie su perfil después.
     *
     * @return array<string, string|null>
     */
    public function snapshotShippingFromProfile(): array
    {
        return [
            'shipping_recipient_name' => $this->name,
            'shipping_phone' => $this->phone,
            'shipping_document_number' => $this->document_number,
            'shipping_address_line1' => $this->address_line1,
            'shipping_address_line2' => $this->address_line2,
            'shipping_city' => $this->city,
            'shipping_state' => $this->state,
            'shipping_postal_code' => $this->postal_code,
            'shipping_country' => $this->country ?? 'DO',
            'shipping_notes' => $this->delivery_instructions,
        ];
    }
}
