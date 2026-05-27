<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Notifications\NotificationType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class NotifyUnclaimedReadyOrdersCommand extends Command
{
    protected $signature = 'orders:notify-unclaimed-ready';

    protected $description = 'Alerta a operaciones si un pedido listo no fue aceptado por ningún domiciliario';

    public function handle(NotificationService $notifications): int
    {
        $minutes = (int) config('orders.courier_claim_timeout_minutes', 45);
        $cutoff = now()->subMinutes($minutes);

        $orders = Order::query()
            ->where('status', OrderStatus::ReadyForDelivery)
            ->whereNull('courier_id')
            ->whereNotNull('ready_at')
            ->where('ready_at', '<=', $cutoff)
            ->with('user')
            ->get();

        $notified = 0;

        foreach ($orders as $order) {
            $cacheKey = 'bf:unclaimed-notified:'.$order->id;

            if (Cache::has($cacheKey)) {
                continue;
            }

            $notifications->notifyType(NotificationType::OrderUnassigned, [
                'order' => $order,
                'order_id' => $order->id,
                'customer_name' => $order->shipping_recipient_name,
            ]);

            Cache::put($cacheKey, true, now()->addHours(12));
            $notified++;
        }

        $this->info("Alertas enviadas: {$notified}");

        return self::SUCCESS;
    }
}
