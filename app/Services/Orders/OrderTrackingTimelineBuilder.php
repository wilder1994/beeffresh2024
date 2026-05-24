<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Collection;

final class OrderTrackingTimelineBuilder
{
    /** @return list<OrderStatus> */
    private function deliveryPath(): array
    {
        return [
            OrderStatus::Pending,
            OrderStatus::Preparing,
            OrderStatus::ReadyForDelivery,
            OrderStatus::PickedUp,
            OrderStatus::InTransit,
            OrderStatus::Delivered,
        ];
    }

    /**
     * @return list<array{
     *     status: string,
     *     label: string,
     *     state: 'completed'|'upcoming',
     *     created_at: string|null,
     * }>
     */
    public function build(Order $order): array
    {
        $logs = $this->sortedLogs($order);
        $path = $this->deliveryPath();
        $currentIndex = $this->currentPathIndex($order->status, $path);

        if (in_array($order->status, OrderStatus::terminalStatuses(), true)) {
            return $this->buildFromLogsOnly($logs);
        }

        if ($currentIndex === null) {
            return $this->buildFromLogsOnly($logs);
        }

        $entries = [];

        foreach ($path as $index => $step) {
            $log = $logs->first(
                fn (OrderStatusLog $entry): bool => $entry->to_status === $step,
            );

            if ($log !== null) {
                $entries[] = $this->completedEntry($log);

                continue;
            }

            if ($index > $currentIndex) {
                $entries[] = $this->upcomingEntry($step);
            }
        }

        return $entries;
    }

    /**
     * @param  list<OrderStatus>  $path
     */
    private function currentPathIndex(OrderStatus $status, array $path): ?int
    {
        foreach ($path as $index => $step) {
            if ($step === $status) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, OrderStatusLog>
     */
    private function sortedLogs(Order $order): Collection
    {
        if (! $order->relationLoaded('statusLogs')) {
            $order->load(['statusLogs' => fn ($query) => $query->oldest()]);
        }

        return $order->statusLogs->sortBy('created_at')->values();
    }

    /**
     * @return list<array{
     *     status: string,
     *     label: string,
     *     state: 'completed'|'upcoming',
     *     created_at: string|null,
     * }>
     */
    private function buildFromLogsOnly(Collection $logs): array
    {
        return $logs
            ->map(fn (OrderStatusLog $log): array => $this->completedEntry($log))
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     status: string,
     *     label: string,
     *     state: 'completed',
     *     created_at: string|null,
     * }
     */
    private function completedEntry(OrderStatusLog $log): array
    {
        $label = $log->to_status->label();

        return [
            'status' => $log->to_status->value,
            'to_status' => $log->to_status->value,
            'label' => $label,
            'to_status_label' => $label,
            'state' => 'completed',
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{
     *     status: string,
     *     label: string,
     *     state: 'upcoming',
     *     created_at: null,
     * }
     */
    private function upcomingEntry(OrderStatus $status): array
    {
        $label = $status->label();

        return [
            'status' => $status->value,
            'to_status' => $status->value,
            'label' => $label,
            'to_status_label' => $label,
            'state' => 'upcoming',
            'created_at' => null,
        ];
    }
}
