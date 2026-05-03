<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('logos')->where('tipo', 'administrador')->delete();
    }

    public function down(): void
    {
        // Sin restauración de binarios; la foto de perfil pasa a users.avatar_path
    }
};
