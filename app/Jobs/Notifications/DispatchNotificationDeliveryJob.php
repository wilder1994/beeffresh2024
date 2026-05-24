<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationDeliveryStatus;
use App\Models\NotificationDelivery;
use App\Repositories\Notifications\NotificationRepository;
use App\Services\Notifications\NotificationChannelManager;
use App\Services\Notifications\NotificationContentBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class DispatchNotificationDeliveryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public int $timeout;

    /** @var list<int> */
    public array $backoff;

    public function __construct(
        public readonly int $deliveryId,
    ) {
        $this->tries = (int) config('notifications.job.tries', 3);
        $this->timeout = (int) config('notifications.job.timeout', 60);
        $this->backoff = config('notifications.job.backoff', [30, 120, 300]);
    }

    public function handle(
        NotificationRepository $repository,
        NotificationChannelManager $channels,
        NotificationContentBuilder $contentBuilder,
    ): void {
        $delivery = NotificationDelivery::query()->find($this->deliveryId);

        if ($delivery === null) {
            return;
        }

        if ($delivery->status === NotificationDeliveryStatus::Sent) {
            return;
        }

        $repository->incrementAttempt($delivery);
        $repository->markDeliveryQueued($delivery->fresh());

        $content = $contentBuilder->build(
            $delivery->type,
            $delivery->payload ?? [],
        );

        $result = $channels->driver($delivery->channel)->send($delivery, $content);

        $delivery->refresh();

        if ($result->success) {
            $repository->markDeliverySent($delivery, $result->metadata);

            return;
        }

        if (($result->metadata['skipped'] ?? false) === true) {
            $repository->markDeliverySkipped($delivery, $result->errorMessage ?? 'Skipped');

            return;
        }

        $repository->markDeliveryFailed($delivery, $result->errorMessage ?? 'Unknown error', $result->metadata);

        throw new \RuntimeException($result->errorMessage ?? 'Notification delivery failed');
    }

    public function failed(\Throwable $exception): void
    {
        $delivery = NotificationDelivery::query()->find($this->deliveryId);

        if ($delivery === null) {
            return;
        }

        app(NotificationRepository::class)->markDeliveryFailed(
            $delivery,
            $exception->getMessage(),
            ['failed_job' => true],
        );

        Log::channel('single')->error('Notification delivery job failed permanently', [
            'delivery_id' => $this->deliveryId,
            'error' => $exception->getMessage(),
        ]);
    }
}
