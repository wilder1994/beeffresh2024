<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Tests\TestCase;

class SupplierNotificationCenterTest extends TestCase
{
    public function test_supplier_can_open_notification_center_and_feed(): void
    {
        $this->seed(DemoUsersSeeder::class);

        $supplier = User::query()
            ->where('email', 'proveedor1@demo.beeffresh.test')
            ->firstOrFail();

        $this->actingAs($supplier)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Centro de notificaciones')
            ->assertSee('Sonido en el navegador');

        $this->actingAs($supplier)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJsonStructure(['unread_count', 'notifications']);
    }

    public function test_supplier_portal_includes_notification_bell(): void
    {
        $this->seed(DemoUsersSeeder::class);

        $supplier = User::query()
            ->where('email', 'proveedor1@demo.beeffresh.test')
            ->firstOrFail();

        $this->actingAs($supplier)
            ->get(route('supplier.home'))
            ->assertOk()
            ->assertSee('data-notification-bell');
    }
}
