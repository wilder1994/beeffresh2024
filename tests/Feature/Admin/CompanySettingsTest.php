<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_company_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.configuracion.empresa', ['tab' => 'ubicacion']))
            ->assertOk()
            ->assertSee('mapa operativo', false);
    }

    public function test_non_admin_cannot_access_company_settings(): void
    {
        $dispatcher = User::factory()->employee()->create();

        $this->actingAs($dispatcher)
            ->get(route('admin.configuracion.empresa'))
            ->assertForbidden();
    }

    public function test_admin_can_update_location_coordinates(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = CompanyProfile::singleton();

        $this->actingAs($admin)
            ->put(route('admin.configuracion.empresa.ubicacion'), [
                'company_store_address' => 'Calle 50 # 40-10',
                'company_store_neighborhood' => 'La Candelaria',
                'company_store_city' => 'Medellín',
                'company_store_state' => 'Antioquia',
                'company_store_latitude' => 6.25184,
                'company_store_longitude' => -75.56359,
            ])
            ->assertRedirect(route('admin.configuracion.empresa', ['tab' => 'ubicacion']));

        $profile->refresh();

        $this->assertSame('Calle 50 # 40-10', $profile->store_address);
        $this->assertEqualsWithDelta(6.25184, (float) $profile->store_latitude, 0.00001);
        $this->assertEqualsWithDelta(-75.56359, (float) $profile->store_longitude, 0.00001);
    }

    public function test_legacy_empresa_route_redirects_to_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.empresa.edit'))
            ->assertRedirect(route('admin.configuracion.empresa', ['tab' => 'nosotros']));
    }
}
