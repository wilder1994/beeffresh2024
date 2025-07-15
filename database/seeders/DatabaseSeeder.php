<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Sembrar la base de datos de la aplicación.
     */
    public function run(): void
    {
        // Se desactivan las fábricas de usuarios y productos
        // para que los registros se creen uno por uno manualmente.

        /*
        \App\Models\User::factory(10)->create();
        \App\Models\Producto::factory(100)->create();

        \App\Models\User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@email.co',
        ]);
        */
    }
}
