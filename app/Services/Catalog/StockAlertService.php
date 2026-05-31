<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Enums\Notifications\NotificationType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Collection;

final class StockAlertService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    /**
     * Notifica a operaciones (campana) los productos que acaban de quedar sin stock,
     * incluyendo los combos/packs afectados.
     *
     * @param  list<int>  $productIds
     */
    public function notifyDepleted(array $productIds): void
    {
        $productIds = array_values(array_unique(array_filter($productIds)));

        if ($productIds === []) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('stock', '<=', 0)
            ->get();

        foreach ($products as $product) {
            $offers = $this->affectedOffers($product->id);

            $this->notifications->notifyType(NotificationType::InventoryOutOfStock, [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'affected_offers' => $offers->isNotEmpty()
                    ? $offers->pluck('name')->implode(', ')
                    : 'ninguno',
                'affected_offers_count' => $offers->count(),
            ]);
        }
    }

    /**
     * @return Collection<int, Offer>
     */
    private function affectedOffers(int $productId): Collection
    {
        return Offer::query()
            ->where('is_active', true)
            ->where(function ($query) use ($productId): void {
                $query->where('product_id', $productId)
                    ->orWhereHas('items', fn ($items) => $items->where('product_id', $productId));
            })
            ->get();
    }
}
