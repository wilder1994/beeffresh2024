<?php

declare(strict_types=1);

namespace App\Events\Catalog;

use App\Models\Product;
use App\Support\Realtime\ProductStockPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProductStockUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Product $product,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('operations.inventory'),
            new PrivateChannel('operations.dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'product.stock.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ProductStockPayload::stockPayload($this->product);
    }
}
