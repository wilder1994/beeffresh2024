<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateBeeffreshUser extends Command
{
    protected $signature = 'beeffresh:user
                            {--email= : Correo electrónico}
                            {--name= : Nombre completo}
                            {--password= : Contraseña (opcional; se solicita si falta)}
                            {--role=admin : Rol: admin, cashier, order_clerk, delivery, customer}';

    protected $description = 'Crea un usuario con rol de personal o cliente (Beeffresh).';

    public function handle(): int
    {
        $email = $this->option('email') ?: (string) $this->ask('Correo');
        $name = $this->option('name') ?: (string) $this->ask('Nombre completo');

        $password = $this->option('password');
        if ($password === null || $password === '') {
            $password = (string) $this->secret('Contraseña');
        }

        $roleRaw = (string) ($this->option('role') ?? 'admin');
        $roleEnum = UserRole::tryFrom($roleRaw);
        if ($roleEnum === null) {
            $this->error('Rol inválido. Use: admin, cashier, order_clerk, delivery, customer');

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

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $roleEnum,
            'email_verified_at' => now(),
        ]);

        $this->info("Usuario {$email} creado con rol {$roleEnum->value}.");

        return self::SUCCESS;
    }
}
