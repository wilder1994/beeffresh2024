<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Users\RoleSlug;
use App\Models\CustomerProfile;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateBeeffreshUser extends Command
{
    protected $signature = 'beeffresh:user
                            {--email= : Correo electrónico}
                            {--name= : Nombre completo}
                            {--password= : Contraseña (opcional; se solicita si falta)}
                            {--role=admin : Rol: admin, employee, customer, supplier}';

    protected $description = 'Crea un usuario (roles Spatie: admin, employee, customer, supplier).';

    public function handle(): int
    {
        $email = $this->option('email') ?: (string) $this->ask('Correo');
        $name = $this->option('name') ?: (string) $this->ask('Nombre completo');

        $password = $this->option('password');
        if ($password === null || $password === '') {
            $password = (string) $this->secret('Contraseña');
        }

        $roleRaw = (string) ($this->option('role') ?? 'admin');
        if (! in_array($roleRaw, RoleSlug::all(), true)) {
            $this->error('Rol inválido. Use: admin, employee, customer, supplier');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'unique:users,email']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $parts = preg_split('/\s+/', trim($name), 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = $parts[0] ?? 'Usuario';
        $last = $parts[1] ?? '';

        $user = User::query()->create([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $user->assignRole($roleRaw);

        if ($roleRaw === RoleSlug::CUSTOMER) {
            CustomerProfile::query()->create([
                'user_id' => $user->id,
                'country' => 'CO',
                'accepts_promotions' => true,
                'loyalty_points' => 0,
                'balance' => 0,
            ]);
        }

        if ($roleRaw === RoleSlug::EMPLOYEE) {
            $pos = Position::query()->where('slug', Position::SLUG_DELIVERY)->first()
                ?? Position::query()->first();
            EmployeeProfile::query()->create([
                'user_id' => $user->id,
                'position_id' => $pos?->id,
            ]);
        }

        if ($roleRaw === RoleSlug::SUPPLIER) {
            SupplierProfile::query()->create([
                'user_id' => $user->id,
                'nit' => 'PENDIENTE',
            ]);
        }

        $this->info("Usuario {$email} creado con rol {$roleRaw}.");

        return self::SUCCESS;
    }
}
