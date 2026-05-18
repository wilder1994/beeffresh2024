<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Support\PostLoginRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_redirects_to_home(): void
    {
        $user = User::factory()->create();

        $this->assertSame(route('home', [], false), PostLoginRedirect::path($user));
    }

    public function test_staff_redirects_to_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertSame(route('dashboard', [], false), PostLoginRedirect::path($user));
    }

    public function test_supplier_redirects_to_portal(): void
    {
        $user = User::factory()->supplier()->create();

        $this->assertSame(route('supplier.home', [], false), PostLoginRedirect::path($user));
    }
}
