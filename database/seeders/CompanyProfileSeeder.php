<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::query()->updateOrCreate(
            ['id' => CompanyProfile::SINGLETON_ID],
            [
                'legal_name' => 'BEEF FRESH S.A.S.',
                'trade_name' => 'BEEF FRESH',
                'nit' => '900123456-1',
                'contact_phone' => '+57 602 555 0100',
                'contact_email' => 'contacto@beeffresh.test',
                'store_address' => 'Centro Comercial Jardín Plaza, Cra. 98 #16-200 Local 12',
                'store_neighborhood' => 'Comuna 17',
                'store_city' => 'Cali',
                'store_state' => 'Valle del Cauca',
                'store_latitude' => 3.367842,
                'store_longitude' => -76.531128,
                'about_heading' => 'Sobre BEEF FRESH',
                'about_content' => 'Nos especializamos en la distribución de carnes de alta calidad, garantizando frescura y trazabilidad en cada corte. Somos una empresa comprometida con la salud, el sabor y la satisfacción de nuestros clientes.',
                'promise_heading' => 'Nuestra promesa',
                'promise_content' => 'Compra 100% en línea, entregas puntuales, asesoría personalizada y un equipo experto en carnes que asegura calidad y confianza en cada pedido.',
                'social_heading' => 'Síguenos',
            ]
        );
    }
}
