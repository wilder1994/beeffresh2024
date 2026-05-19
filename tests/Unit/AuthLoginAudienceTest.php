<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\AuthLoginAudience;
use PHPUnit\Framework\TestCase;

class AuthLoginAudienceTest extends TestCase
{
    public function test_defaults_to_client(): void
    {
        $this->assertSame(AuthLoginAudience::CLIENT, AuthLoginAudience::resolve(null));
    }

    public function test_resolves_valid_audiences(): void
    {
        $this->assertSame(AuthLoginAudience::EMPLOYEE, AuthLoginAudience::resolve('empleado'));
        $this->assertSame(AuthLoginAudience::SUPPLIER, AuthLoginAudience::resolve('proveedor'));
    }

    public function test_only_client_can_self_register(): void
    {
        $this->assertTrue(AuthLoginAudience::allowsSelfRegistration(AuthLoginAudience::CLIENT));
        $this->assertFalse(AuthLoginAudience::allowsSelfRegistration(AuthLoginAudience::EMPLOYEE));
        $this->assertFalse(AuthLoginAudience::allowsSelfRegistration(AuthLoginAudience::SUPPLIER));
    }
}
