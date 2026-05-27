<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\Notifications\NotificationType;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFeedScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_feed_unread_scope_returns_only_unread_notifications(): void
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();

        $unread = Notification::query()->create([
            'user_id' => $dispatcher->id,
            'type' => NotificationType::OrderPreparing,
            'title' => 'Pedido sin leer',
            'body' => 'Cuerpo',
            'payload' => [],
        ]);

        $read = Notification::query()->create([
            'user_id' => $dispatcher->id,
            'type' => NotificationType::OrderPreparing,
            'title' => 'Pedido leído',
            'body' => 'Cuerpo',
            'payload' => [],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($dispatcher)
            ->getJson(route('notifications.feed', ['scope' => 'unread']))
            ->assertOk();

        $ids = collect($response->json('notifications'))->pluck('id')->all();

        $this->assertContains($unread->id, $ids);
        $this->assertNotContains($read->id, $ids);
        $this->assertSame(1, $response->json('unread_count'));
    }

    public function test_history_returns_paginated_notifications_including_read(): void
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();

        Notification::query()->create([
            'user_id' => $dispatcher->id,
            'type' => NotificationType::OrderPreparing,
            'title' => 'Leída',
            'body' => 'Cuerpo',
            'payload' => [],
            'read_at' => now(),
        ]);

        Notification::query()->create([
            'user_id' => $dispatcher->id,
            'type' => NotificationType::OrderPreparing,
            'title' => 'Sin leer',
            'body' => 'Cuerpo',
            'payload' => [],
        ]);

        $response = $this->actingAs($dispatcher)
            ->getJson(route('notifications.history'))
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->assertCount(2, $response->json('notifications'));
        $this->assertTrue(
            collect($response->json('notifications'))->contains(fn (array $row): bool => $row['read'] === true),
        );
    }
}
