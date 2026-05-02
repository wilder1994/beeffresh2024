<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Usuario con acceso al panel (middleware auth). En local las credenciales
     * vienen de .env; en producción definir ADMIN_* con valores seguros.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@beeffresh.local');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'Administrador Beeffresh'),
                'password' => (string) env('ADMIN_PASSWORD', 'password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
