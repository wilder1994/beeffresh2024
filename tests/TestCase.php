<?php

namespace Tests;

use Database\Seeders\PositionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PositionSeeder::class);
    }
}
