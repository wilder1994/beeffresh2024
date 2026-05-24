<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use App\Models\CustomerProfile;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de prueba: 2 por cada rol Spatie (datos Colombia).
 * Contraseña: config('admin.password') — por defecto "password".
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('admin.password', 'password');

        $definitions = [
            RoleSlug::ADMIN => [
                [
                    'email' => 'admin1@demo.beeffresh.test',
                    'first_name' => 'Ana',
                    'last_name' => 'Rodríguez',
                    'phone' => '3105550101',
                    'document_type' => 'CC',
                    'document_number' => '52123456',
                ],
                [
                    'email' => 'admin2@demo.beeffresh.test',
                    'first_name' => 'Luis',
                    'last_name' => 'Gómez',
                    'phone' => '3205550102',
                    'document_type' => 'CC',
                    'document_number' => '80987654',
                ],
            ],
            RoleSlug::EMPLOYEE => [
                [
                    'email' => 'empleado1@demo.beeffresh.test',
                    'first_name' => 'María',
                    'last_name' => 'Vargas',
                    'phone' => '3155550201',
                    'document_type' => 'CC',
                    'document_number' => '43112233',
                    'position_slug' => 'cajero',
                    'hire_date' => '2023-03-15',
                    'salary' => 1800000,
                    'eps' => 'Sura EPS',
                    'arl' => 'Sura ARL',
                    'emergency_contact' => 'Pedro Vargas',
                    'emergency_phone' => '3108001122',
                    'home_address' => 'Calle 72 # 10-25, Bogotá',
                ],
                [
                    'email' => 'empleado2@demo.beeffresh.test',
                    'first_name' => 'Diego',
                    'last_name' => 'Muñoz',
                    'phone' => '3185550202',
                    'document_type' => 'CC',
                    'document_number' => '1023456789',
                    'position_slug' => Position::SLUG_DELIVERY,
                    'hire_date' => '2022-11-01',
                    'salary' => 2200000,
                    'eps' => 'Sanitas EPS',
                    'arl' => 'Positiva ARL',
                    'emergency_contact' => 'Laura Muñoz',
                    'emergency_phone' => '3009003344',
                    'home_address' => 'Carrera 50 # 45-12, Medellín',
                    'home_city' => 'Medellín',
                    'home_state' => 'Antioquia',
                    'home_country' => 'CO',
                    'home_latitude' => 6.25184,
                    'home_longitude' => -75.56359,
                    'vehicle_type' => 'Motocicleta',
                    'plate_number' => 'ABC123',
                    'driver_license' => 'LIC-998877',
                    'license_expiration' => '2027-06-30',
                    'assigned_zone' => 'Medellín norte',
                    'average_rating' => 4.85,
                    'permissions' => [PermissionKey::MODULE_COURIER],
                ],
                [
                    'email' => 'despachador1@demo.beeffresh.test',
                    'first_name' => 'Sandra',
                    'last_name' => 'Restrepo',
                    'phone' => '3175550203',
                    'document_type' => 'CC',
                    'document_number' => '1034567890',
                    'position_slug' => Position::SLUG_DISPATCH,
                    'hire_date' => '2021-05-10',
                    'salary' => 2500000,
                    'eps' => 'Compensar EPS',
                    'arl' => 'Sura ARL',
                    'emergency_contact' => 'Carlos Restrepo',
                    'emergency_phone' => '3017004455',
                    'home_address' => 'Calle 44 # 70-15, Medellín',
                    'home_city' => 'Medellín',
                    'home_state' => 'Antioquia',
                    'home_country' => 'CO',
                    'home_latitude' => 6.24496,
                    'home_longitude' => -75.58902,
                    'permissions' => [PermissionKey::MODULE_ORDERS],
                ],
            ],
            RoleSlug::CUSTOMER => [
                [
                    'email' => 'cliente1@demo.beeffresh.test',
                    'first_name' => 'Carla',
                    'last_name' => 'Mejía',
                    'phone' => '3001234567',
                    'document_type' => 'CC',
                    'document_number' => '1020304050',
                    'address' => 'Carrera 15 # 85-32, Apartamento 401',
                    'neighborhood' => 'Chicó Norte',
                    'city' => 'Bogotá',
                    'state' => 'Cundinamarca',
                    'postal_code' => '110221',
                    'address_reference' => 'Edificio Torre Verde, portería 24 h',
                    'delivery_notes' => 'Dejar en recepción. Tocar timbre 401.',
                    'loyalty_points' => 120,
                    'balance' => 25000,
                    'latitude' => 4.669595,
                    'longitude' => -74.055717,
                ],
                [
                    'email' => 'cliente2@demo.beeffresh.test',
                    'first_name' => 'Jorge',
                    'last_name' => 'Castaño',
                    'phone' => '3019876543',
                    'document_type' => 'CC',
                    'document_number' => '9876543210',
                    'address' => 'Calle 10 # 43A-15',
                    'neighborhood' => 'El Poblado',
                    'city' => 'Medellín',
                    'state' => 'Antioquia',
                    'postal_code' => '050021',
                    'address_reference' => 'Casa blanca, portón negro',
                    'delivery_notes' => 'Llamar al llegar.',
                    'loyalty_points' => 45,
                    'balance' => 0,
                    'latitude' => 6.208869,
                    'longitude' => -75.567983,
                ],
            ],
            RoleSlug::SUPPLIER => [
                [
                    'email' => 'proveedor1@demo.beeffresh.test',
                    'first_name' => 'Rosa',
                    'last_name' => 'Herrera',
                    'phone' => '3145550301',
                    'document_type' => 'CC',
                    'document_number' => '39667788',
                    'company' => 'Carnes del Campo SAS',
                    'nit' => '900123456-1',
                    'contact_name' => 'Rosa Herrera',
                    'business_phone' => '6045551001',
                    'business_email' => 'compras@carnesdelcampo.co',
                    'business_address' => 'Km 5 Vía Rionegro, Bodega 12',
                    'city' => 'Medellín',
                    'bank_name' => 'Bancolombia',
                    'account_type' => 'Ahorros',
                    'account_number' => '12345678901',
                    'credit_days' => 30,
                ],
                [
                    'email' => 'proveedor2@demo.beeffresh.test',
                    'first_name' => 'Miguel',
                    'last_name' => 'Torres',
                    'phone' => '3165550302',
                    'document_type' => 'CC',
                    'document_number' => '71234567',
                    'company' => 'Distribuidora Llanos Ltda',
                    'nit' => '900987654-3',
                    'contact_name' => 'Miguel Torres',
                    'business_phone' => '6015552002',
                    'business_email' => 'ventas@distribuidorallanos.co',
                    'business_address' => 'Zona Industrial Puente Aranda, Lote 8',
                    'city' => 'Bogotá',
                    'bank_name' => 'Davivienda',
                    'account_type' => 'Corriente',
                    'account_number' => '98765432100',
                    'credit_days' => 15,
                ],
            ],
        ];

        foreach ($definitions as $role => $users) {
            foreach ($users as $row) {
                $this->seedUser($role, $row, $password);
            }
        }

        if ($this->command !== null) {
            $this->command->info('Usuarios demo (Colombia) creados. Contraseña: '.$password);
            $this->command->table(
                ['Rol', 'Correo'],
                collect($definitions)->flatMap(
                    fn (array $users, string $role) => collect($users)->map(
                        fn (array $u) => [RoleSlug::label($role), $u['email']]
                    )
                )->all()
            );
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function seedUser(string $role, array $row, string $password): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => $row['email']],
            [
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'password' => $password,
                'email_verified_at' => now(),
                'status' => 'active',
                'phone' => $row['phone'] ?? null,
                'document_type' => $row['document_type'] ?? null,
                'document_number' => $row['document_number'] ?? null,
            ]
        );

        $user->syncRoles([$role]);

        if ($role === RoleSlug::EMPLOYEE && ! empty($row['permissions']) && is_array($row['permissions'])) {
            $user->syncPermissions($row['permissions']);
        }

        match ($role) {
            RoleSlug::CUSTOMER => $this->seedCustomerProfile($user, $row),
            RoleSlug::EMPLOYEE => $this->seedEmployeeProfile($user, $row),
            RoleSlug::SUPPLIER => $this->seedSupplierProfile($user, $row),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function seedCustomerProfile(User $user, array $row): void
    {
        CustomerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $row['address'] ?? 'Calle 100 # 7-25',
                'neighborhood' => $row['neighborhood'] ?? 'Chicó',
                'city' => $row['city'] ?? 'Bogotá',
                'state' => $row['state'] ?? 'Cundinamarca',
                'address_reference' => $row['address_reference'] ?? null,
                'delivery_notes' => $row['delivery_notes'] ?? null,
                'accepts_promotions' => true,
                'loyalty_points' => (int) ($row['loyalty_points'] ?? 0),
                'balance' => (float) ($row['balance'] ?? 0),
                'postal_code' => $row['postal_code'] ?? null,
                'country' => 'CO',
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function seedEmployeeProfile(User $user, array $row): void
    {
        $slug = $row['position_slug'] ?? Position::SLUG_DELIVERY;
        $position = Position::query()->where('slug', $slug)->first()
            ?? Position::query()->first();

        EmployeeProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'position_id' => $position?->id,
                'hire_date' => $row['hire_date'] ?? now()->subYear()->toDateString(),
                'salary' => $row['salary'] ?? 2000000,
                'eps' => $row['eps'] ?? 'EPS Sura',
                'arl' => $row['arl'] ?? 'ARL Sura',
                'emergency_contact' => $row['emergency_contact'] ?? 'Contacto emergencia',
                'emergency_phone' => $row['emergency_phone'] ?? '3000000000',
                'home_address' => $row['home_address'] ?? 'Bogotá, Colombia',
                'home_neighborhood' => $row['home_neighborhood'] ?? null,
                'home_city' => $row['home_city'] ?? null,
                'home_state' => $row['home_state'] ?? null,
                'home_country' => $row['home_country'] ?? 'CO',
                'home_latitude' => $row['home_latitude'] ?? null,
                'home_longitude' => $row['home_longitude'] ?? null,
                'notes' => $row['notes'] ?? null,
                'vehicle_type' => $row['vehicle_type'] ?? null,
                'plate_number' => $row['plate_number'] ?? null,
                'driver_license' => $row['driver_license'] ?? null,
                'license_expiration' => $row['license_expiration'] ?? null,
                'available' => true,
                'assigned_zone' => $row['assigned_zone'] ?? null,
                'average_rating' => $row['average_rating'] ?? null,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function seedSupplierProfile(User $user, array $row): void
    {
        SupplierProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $row['company'] ?? 'Proveedor demo SAS',
                'nit' => $row['nit'] ?? '900000000-1',
                'contact_name' => $row['contact_name'] ?? $user->name,
                'business_phone' => $row['business_phone'] ?? $row['phone'] ?? null,
                'business_email' => $row['business_email'] ?? $user->email,
                'business_address' => $row['business_address'] ?? 'Bogotá, Colombia',
                'city' => $row['city'] ?? 'Bogotá',
                'bank_name' => $row['bank_name'] ?? 'Bancolombia',
                'account_type' => $row['account_type'] ?? 'Ahorros',
                'account_number' => $row['account_number'] ?? '00000000000',
                'credit_days' => (int) ($row['credit_days'] ?? 30),
            ]
        );
    }
}
