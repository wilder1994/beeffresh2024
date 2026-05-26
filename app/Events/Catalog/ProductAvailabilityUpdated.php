<?php

declare(strict_types=1);

namespace App\Events\Catalog;

use App\Models\Product;
use App\Support\Realtime\ProductStockPayload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProductAvailabilityUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Product $product,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new Channel('store.catalog'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'product.availability.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ProductStockPayload::availabilityPayload($this->product);
    }
}
