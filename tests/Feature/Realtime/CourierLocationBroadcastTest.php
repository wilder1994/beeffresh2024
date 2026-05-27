<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Events\Couriers\CourierLocationUpdated;
use App\Jobs\Realtime\BroadcastCourierLocationJob;
use App\Models\User;
use App\Services\Orders\CourierLocationService;
use App\Support\Couriers\CourierLocationRateLimiter;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CourierLocationBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_rate_limiter_blocks_rapid_duplicate_positions(): void
    {
        $limiter = app(CourierLocationRateLimiter::class);
        Cache::flush();

        $this->assertTrue($limiter->shouldBroadcast(1, 6.2442, -75.5812));
        $this->assertFalse($limiter->shouldBroadcast(1, 6.2442, -75.5812));
    }

    public function test_rate_limiter_allows_faster_updates_on_active_route(): void
    {
        $limiter = app(CourierLocationRateLimiter::class);
        Cache::flush();

        $this->assertTrue($limiter->shouldBroadcast(2, 3.4516, -76.5320, true));
        $this->assertFalse($limiter->shouldBroadcast(2, 3.45161, -76.53201, true));
        sleep(3);
        $this->assertTrue($limiter->shouldBroadcast(2, 3.4525, -76.5330, true));
    }

    public function test_courier_location_record_dispatches_throttled_broadcast_job(): void
    {
        Queue::fake();
        Event::fake([CourierLocationUpdated::class]);

        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();
        $service = app(CourierLocationService::class);

        $service->record($courier, 6.2442, -75.5812, 10.0);
        $service->record($courier, 6.2442, -75.5812, 10.0);

        Queue::assertPushed(BroadcastCourierLocationJob::class, 1);
    }

    public function test_courier_location_updated_event_uses_expected_channels(): void
    {
        $event = new CourierLocationUpdated([
            'courier_id' => 5,
            'order_id' => 12,
            'lat' => 6.24,
            'lng' => -75.58,
        ]);

        $channels = collect($event->broadcastOn())->map->name->all();

        $this->assertContains('private-operations.map', $channels);
        $this->assertContains('private-couriers.5', $channels);
        $this->assertContains('private-orders.12', $channels);
        $this->assertSame('courier.location.updated', $event->broadcastAs());
    }
}
