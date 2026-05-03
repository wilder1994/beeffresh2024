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
        User::updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'password' => config('admin.password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
