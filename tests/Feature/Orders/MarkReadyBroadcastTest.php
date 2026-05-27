<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderUpdated;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\CourierAssignmentService;
use App\Services\Orders\OrderWorkflowService;
use App\Services\Realtime\OrderBroadcastService;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MarkReadyBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_mark_ready_flow_emits_single_order_updated_with_courier(): void
    {
        Event::fake([OrderUpdated::class]);

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => '10000.00',
            'status' => OrderStatus::Preparing,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            ...$customer->snapshotShippingFromProfile(),
        ]);

        $workflow = app(OrderWorkflowService::class);
        $courierAssignment = app(CourierAssignmentService::class);
        $orderBroadcast = app(OrderBroadcastService::class);

        $order = $workflow->transitionSilent($order, OrderStatus::ReadyForDelivery, $dispatcher);

        DB::transaction(function () use ($orderBroadcast, $order): void {
            $orderBroadcast->dispatch($order->fresh(['user', 'courier', 'items']));
        });

        $courierAssignment->claimByCourier($order->fresh(), $courier);

        Event::assertDispatched(OrderUpdated::class, function (OrderUpdated $event) use ($order, $courier): bool {
            return $event->order->id === $order->id
                && $event->order->courier_id === $courier->id
                && $event->order->status === OrderStatus::ReadyForDelivery;
        });
    }
}
