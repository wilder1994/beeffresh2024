<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtimeHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_staff_can_fetch_realtime_health_json(): void
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($dispatcher)
            ->getJson(route('admin.realtime.health'))
            ->assertOk()
            ->assertJsonStructure([
                'websocket_connected',
                'queue_healthy',
                'fallback_mode',
                'pending_jobs',
                'mode',
                'checked_at',
            ])
            ->assertJsonPath('mode', fn (string $mode): bool => in_array($mode, ['live', 'degraded', 'fallback'], true));
    }

    public function test_guest_cannot_fetch_realtime_health(): void
    {
        $this->getJson(route('admin.realtime.health'))
            ->assertUnauthorized();
    }
}
