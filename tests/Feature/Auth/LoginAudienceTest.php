<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAudienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_login_shows_registration_prompt(): void
    {
        $response = $this->get('/login?tipo=cliente');

        $response->assertOk();
        $response->assertSee('Regístrate aquí', false);
        $response->assertSee('register-client-confirm', false);
    }

    public function test_employee_login_hides_registration_prompt(): void
    {
        $response = $this->get('/login?tipo=empleado');

        $response->assertOk();
        $response->assertDontSee('Regístrate aquí', false);
        $response->assertSee('personal interno', false);
    }

    public function test_home_shows_register_in_nav_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Registrarse', false);
        $response->assertSee('register-client-confirm', false);
    }

    public function test_home_with_registro_confirm_includes_confirm_modal(): void
    {
        $response = $this->get('/?registro=confirm');

        $response->assertOk();
        $response->assertSee('Te vas a registrar como', false);
    }
}
