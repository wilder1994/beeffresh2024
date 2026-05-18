<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use App\Models\CustomerProfile;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Support\UserAvatarStorage;
use Illuminate\Support\Facades\Storage;

final class AdminUserPersistence
{
    /**
     * @param  array<string, mixed>  $v
     */
    public function persist(array $v, ?User $existing, ?UploadedFile $avatar): User
    {
        return DB::transaction(function () use ($v, $existing, $avatar) {
            $role = (string) $v['role_slug'];
            $user = $existing ?? new User;

            $fill = [
                'first_name' => (string) $v['first_name'],
                'last_name' => (string) $v['last_name'],
                'document_type' => $v['document_type'] ?? null,
                'document_number' => $v['document_number'] ?? null,
                'phone' => $v['phone'] ?? null,
                'email' => (string) $v['email'],
                'status' => (string) ($v['status'] ?? 'active'),
            ];
            if (! empty($v['password'])) {
                $fill['password'] = Hash::make((string) $v['password']);
            }
            $user->fill($fill);
            $user->save();

            if ($avatar instanceof UploadedFile) {
                $user->avatar = UserAvatarStorage::replace($user->avatar, $avatar);
                $user->save();
            }

            $user->syncRoles([$role]);

            if ($role === RoleSlug::EMPLOYEE) {
                $selected = array_values(array_intersect(
                    PermissionKey::employeeModuleKeys(),
                    is_array($v['permissions'] ?? null) ? $v['permissions'] : []
                ));
                $user->syncPermissions($selected);
            } else {
                $user->syncPermissions([]);
            }

            EmployeeProfile::query()->where('user_id', $user->id)->delete();
            CustomerProfile::query()->where('user_id', $user->id)->delete();
            SupplierProfile::query()->where('user_id', $user->id)->delete();

            match ($role) {
                RoleSlug::CUSTOMER => $this->upsertCustomer($user, $v),
                RoleSlug::EMPLOYEE => $this->upsertEmployee($user, $v),
                RoleSlug::SUPPLIER => $this->upsertSupplier($user, $v),
                default => null,
            };

            return $user->fresh(['roles', 'employeeProfile.position', 'customerProfile', 'supplierProfile']);
        });
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function upsertCustomer(User $user, array $v): void
    {
        CustomerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $v['customer_address'] ?? null,
                'neighborhood' => $v['customer_neighborhood'] ?? null,
                'city' => $v['customer_city'] ?? null,
                'state' => $v['customer_state'] ?? null,
                'address_reference' => $v['customer_address_reference'] ?? null,
                'delivery_notes' => $v['customer_delivery_notes'] ?? null,
                'accepts_promotions' => (bool) ($v['customer_accepts_promotions'] ?? true),
                'loyalty_points' => (int) ($v['customer_loyalty_points'] ?? 0),
                'balance' => (string) ($v['customer_balance'] ?? '0'),
                'postal_code' => $v['customer_postal_code'] ?? null,
                'country' => $v['customer_country'] ?? 'DO',
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function upsertEmployee(User $user, array $v): void
    {
        $positionId = isset($v['employee_position_id']) ? (int) $v['employee_position_id'] : null;
        $position = $positionId ? Position::query()->find($positionId) : null;
        $isDelivery = $position?->isDelivery() ?? false;

        EmployeeProfile::query()->create([
            'user_id' => $user->id,
            'position_id' => $positionId,
            'hire_date' => $v['employee_hire_date'] ?? null,
            'salary' => $v['employee_salary'] ?? null,
            'eps' => $v['employee_eps'] ?? null,
            'arl' => $v['employee_arl'] ?? null,
            'emergency_contact' => $v['employee_emergency_contact'] ?? null,
            'emergency_phone' => $v['employee_emergency_phone'] ?? null,
            'home_address' => $v['employee_home_address'] ?? null,
            'notes' => $v['employee_notes'] ?? null,
            'vehicle_type' => $isDelivery ? ($v['employee_vehicle_type'] ?? null) : null,
            'plate_number' => $isDelivery ? ($v['employee_plate_number'] ?? null) : null,
            'driver_license' => $isDelivery ? ($v['employee_driver_license'] ?? null) : null,
            'license_expiration' => $isDelivery ? ($v['employee_license_expiration'] ?? null) : null,
            'available' => $isDelivery ? (bool) ($v['employee_available'] ?? true) : true,
            'assigned_zone' => $isDelivery ? ($v['employee_assigned_zone'] ?? null) : null,
            'average_rating' => $isDelivery ? ($v['employee_average_rating'] ?? null) : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function upsertSupplier(User $user, array $v): void
    {
        SupplierProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $v['supplier_company_name'] ?? null,
                'nit' => $v['supplier_nit'] ?? null,
                'contact_name' => $v['supplier_contact_name'] ?? null,
                'business_phone' => $v['supplier_business_phone'] ?? null,
                'business_email' => $v['supplier_business_email'] ?? null,
                'business_address' => $v['supplier_business_address'] ?? null,
                'city' => $v['supplier_city'] ?? null,
                'bank_name' => $v['supplier_bank_name'] ?? null,
                'account_type' => $v['supplier_account_type'] ?? null,
                'account_number' => $v['supplier_account_number'] ?? null,
                'credit_days' => isset($v['supplier_credit_days']) ? (int) $v['supplier_credit_days'] : null,
            ]
        );
    }
}
