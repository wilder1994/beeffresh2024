<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationDeliveryStatus;
use App\Models\NotificationDelivery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class NotificationMetricsService
{
    /**
     * @return array{
     *   sent: int,
     *   failed: int,
     *   pending: int,
     *   avg_seconds: float|null,
     *   by_channel: list<array{channel: string, label: string, total: int}>
     * }
     */
    public function dashboardSummary(int $days = 7): array
    {
        $since = Carbon::now()->subDays($days);

        $sent = NotificationDelivery::query()
            ->where('status', NotificationDeliveryStatus::Sent)
            ->where('created_at', '>=', $since)
            ->count();

        $failed = NotificationDelivery::query()
            ->whereIn('status', [NotificationDeliveryStatus::Failed, NotificationDeliveryStatus::Skipped])
            ->where('created_at', '>=', $since)
            ->count();

        $pending = NotificationDelivery::query()
            ->whereIn('status', [NotificationDeliveryStatus::Pending, NotificationDeliveryStatus::Queued])
            ->count();

        $avgSeconds = NotificationDelivery::query()
            ->where('status', NotificationDeliveryStatus::Sent)
            ->whereNotNull('sent_at')
            ->where('created_at', '>=', $since)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, sent_at)) as avg_seconds')
            ->value('avg_seconds');

        $byChannel = NotificationDelivery::query()
            ->select('channel', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $since)
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'channel' => $row->channel instanceof NotificationChannel ? $row->channel->value : (string) $row->channel,
                'label' => $row->channel instanceof NotificationChannel ? $row->channel->label() : (string) $row->channel,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        return [
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'avg_seconds' => $avgSeconds !== null ? round((float) $avgSeconds, 1) : null,
            'by_channel' => $byChannel,
        ];
    }
}
