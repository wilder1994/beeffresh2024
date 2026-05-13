<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Users\RoleSlug;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $full = config('admin.name', 'Administrador');
        $parts = preg_split('/\s+/', trim((string) $full), 2) ?: [];
        $first = $parts[0] ?? 'Administrador';
        $last = $parts[1] ?? 'Sistema';

        $user = User::query()->updateOrCreate(
            ['email' => config('admin.email')],
            [
                'first_name' => $first,
                'last_name' => $last,
                'password' => config('admin.password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        $user->syncRoles([RoleSlug::ADMIN]);
    }
}
