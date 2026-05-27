<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderTrackingFeedRequest;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Services\Orders\OrderTrackingTimelineBuilder;
use App\Support\Orders\CustomerTrackingMapPhase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderTrackingController extends Controller
{
    public function __construct(
        private readonly OrderTrackingTimelineBuilder $timelineBuilder,
    ) {}

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order = $this->loadTrackingDetail($order);

        return $this->trackingView($order, $order->tracking_token);
    }

    public function showByToken(string $trackingToken): View
    {
        $order = $this->resolveByToken($trackingToken);
        $order = $this->loadTrackingDetail($order);

        return $this->trackingView($order, $trackingToken);
    }

    private function trackingView(Order $order, string $trackingToken): View
    {
        return view('store.orders.tracking', [
            'order' => $order,
            'timeline' => $this->timelineBuilder->build($order),
            'trackingToken' => $trackingToken,
            'mapPhase' => CustomerTrackingMapPhase::forOrder($order),
            'courierLocation' => $this->courierLocationFor($order),
            'mapsApiKey' => config('services.google.maps_api_key'),
        ]);
    }

    public function feed(OrderTrackingFeedRequest $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json($this->trackingPayload($order));
    }

    public function feedByToken(OrderTrackingFeedRequest $request, string $trackingToken): JsonResponse
    {
        $order = $this->resolveByToken($trackingToken);

        return response()->json($this->trackingPayload($order));
    }

    private function resolveByToken(string $trackingToken): Order
    {
        $order = Order::query()
            ->where('tracking_token', $trackingToken)
            ->first();

        if ($order === null) {
            throw new NotFoundHttpException('Seguimiento no encontrado.');
        }

        return $order;
    }

    private function loadTrackingDetail(Order $order): Order
    {
        return $order->load([
            'courier:id,first_name,last_name',
            'statusLogs' => fn ($q) => $q->oldest(),
            'items.product',
            'items.offer',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function trackingPayload(Order $order): array
    {
        $order->loadMissing([
            'courier:id,first_name,last_name',
            'statusLogs' => fn ($q) => $q->oldest(),
        ]);

        return [
            'generated_at' => now()->toIso8601String(),
            'order' => [
                'id' => $order->id,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'courier_name' => $order->courier?->name,
                'assigned_at' => $order->assigned_at?->toIso8601String(),
                'ready_at' => $order->ready_at?->toIso8601String(),
                'picked_up_at' => $order->picked_up_at?->toIso8601String(),
                'delivered_at' => $order->delivered_at?->toIso8601String(),
                'updated_at' => $order->updated_at?->toIso8601String(),
                'shipping_latitude' => $order->shipping_latitude !== null ? (float) $order->shipping_latitude : null,
                'shipping_longitude' => $order->shipping_longitude !== null ? (float) $order->shipping_longitude : null,
            ],
            'timeline' => $this->timelineBuilder->build($order),
            'map_phase' => CustomerTrackingMapPhase::forOrder($order),
            'destination' => [
                'lat' => $order->shipping_latitude !== null ? (float) $order->shipping_latitude : null,
                'lng' => $order->shipping_longitude !== null ? (float) $order->shipping_longitude : null,
            ],
            'courier_location' => $this->courierLocationFor($order),
        ];
    }

    /**
     * @return array{lat: float, lng: float, updated_at: string|null}|null
     */
    private function courierLocationFor(Order $order): ?array
    {
        if ($order->courier_id === null) {
            return null;
        }

        $latest = CourierLocation::query()
            ->where('user_id', $order->courier_id)
            ->latest('recorded_at')
            ->first();

        if ($latest === null) {
            return null;
        }

        return [
            'lat' => (float) $latest->latitude,
            'lng' => (float) $latest->longitude,
            'updated_at' => $latest->recorded_at?->toIso8601String(),
        ];
    }
}
