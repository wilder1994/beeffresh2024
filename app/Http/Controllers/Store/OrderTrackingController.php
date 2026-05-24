<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderTrackingFeedRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderTrackingController extends Controller
{
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        return view('store.orders.tracking', [
            'order' => $this->loadTrackingDetail($order),
            'trackingToken' => $order->tracking_token,
        ]);
    }

    public function showByToken(string $trackingToken): View
    {
        $order = $this->resolveByToken($trackingToken);

        return view('store.orders.tracking', [
            'order' => $this->loadTrackingDetail($order),
            'trackingToken' => $trackingToken,
        ]);
    }

    public function feed(OrderTrackingFeedRequest $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json($this->trackingPayload($order, $request->since()));
    }

    public function feedByToken(OrderTrackingFeedRequest $request, string $trackingToken): JsonResponse
    {
        $order = $this->resolveByToken($trackingToken);

        return response()->json($this->trackingPayload($order, $request->since()));
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
            'statusLogs' => fn ($q) => $q->latest()->limit(20),
            'items.product',
            'items.offer',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function trackingPayload(Order $order, ?string $since): array
    {
        $order->loadMissing(['courier:id,first_name,last_name', 'statusLogs']);

        $logs = $order->statusLogs->sortBy('created_at')->values();

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
            ],
            'timeline' => $logs->values()->map(fn ($log): array => [
                'from_status' => $log->from_status?->value,
                'to_status' => $log->to_status->value,
                'to_status_label' => $log->to_status->label(),
                'note' => $log->note,
                'created_at' => $log->created_at?->toIso8601String(),
            ]),
        ];
    }
}
