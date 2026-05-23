<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('about_heading', 160);
            $table->text('about_content');
            $table->string('promise_heading', 160);
            $table->text('promise_content');
            $table->string('social_heading', 160);
            $table->string('social_facebook', 500)->nullable();
            $table->string('social_instagram', 500)->nullable();
            $table->string('social_twitter', 500)->nullable();
            $table->string('social_whatsapp', 500)->nullable();
            $table->string('social_tiktok', 500)->nullable();
            $table->string('social_youtube', 500)->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('company_profiles')->insert([
            'id' => 1,
            'about_heading' => 'Sobre BEEF FRESH',
            'about_content' => 'Nos especializamos en la distribución de carnes de alta calidad, garantizando frescura y trazabilidad en cada corte. Somos una empresa comprometida con la salud, el sabor y la satisfacción de nuestros clientes.',
            'promise_heading' => 'Nuestra promesa',
            'promise_content' => 'Compra 100% en línea, entregas puntuales, asesoría personalizada y un equipo experto en carnes que asegura calidad y confianza en cada pedido.',
            'social_heading' => 'Síguenos',
            'social_facebook' => null,
            'social_instagram' => null,
            'social_twitter' => null,
            'social_whatsapp' => null,
            'social_tiktok' => null,
            'social_youtube' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
