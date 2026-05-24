<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Notifications\NotificationType;
use App\Enums\OrderStatus;
use App\Events\Orders\OrderDelayed;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class CheckDelayedOrdersCommand extends Command
{
    protected $signature = 'notifications:check-delayed-orders';

    protected $description = 'Notifica pedidos operacionales retrasados según configuración.';

    public function handle(): int
    {
        $minutes = (int) config('notifications.delayed_order_minutes', 45);
        $threshold = Carbon::now()->subMinutes($minutes);

        $statuses = [
            OrderStatus::Pending,
            OrderStatus::Preparing,
            OrderStatus::ReadyForDelivery,
        ];

        Order::query()
            ->whereIn('status', array_map(fn (OrderStatus $s) => $s->value, $statuses))
            ->where('updated_at', '<=', $threshold)
            ->each(function (Order $order): void {
                $alreadyNotified = Notification::query()
                    ->where('type', NotificationType::OrderDelayed)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->whereJsonContains('payload->order_id', $order->id)
                    ->exists();

                if (! $alreadyNotified) {
                    event(new OrderDelayed($order));
                }
            });

        $this->info('Delayed order scan completed.');

        return self::SUCCESS;
    }
}
