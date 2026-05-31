<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\AssignCourierOrderRequest;
use App\Http\Requests\Orders\CancelOrderRequest;
use App\Http\Requests\Orders\MarkReadyOrderRequest;
use App\Http\Requests\Orders\OrderOperationsIndexRequest;
use App\Http\Requests\Orders\ReassignDispatcherOrderRequest;
use App\Http\Requests\Orders\RedispatchOrderRequest;
use App\Http\Requests\Orders\StartPreparingOrderRequest;
use App\Models\CompanyProfile;
use App\Models\Order;
use App\Support\Orders\OrderOperationsScope;
use App\Support\Realtime\OrderBroadcastPayload;
use App\Models\User;
use App\Services\Orders\CourierAssignmentService;
use App\Services\Orders\OrderDispatcherAssignmentService;
use App\Services\Orders\OrderOperationsQueryService;
use App\Services\Orders\OrderWorkflowService;
use App\Services\Realtime\OrderBroadcastService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderOperationsController extends Controller
{
    public function __construct(
        private readonly OrderOperationsQueryService $queries,
        private readonly OrderWorkflowService $workflow,
        private readonly CourierAssignmentService $courierAssignment,
        private readonly OrderDispatcherAssignmentService $dispatcherAssignment,
        private readonly OrderBroadcastService $orderBroadcast,
    ) {}

    public function index(OrderOperationsIndexRequest $request): View
    {
        $user = $request->user();
        $tab = $request->tab();
        $search = $request->search();
        $filters = array_merge(
            $this->filtersFromTab($tab, $search),
            ['scope_user' => $user],
        );

        return view('admin.pedidos.index', [
            'metrics' => $user->isDispatcher()
                ? $this->queries->metricsForUser($user)
                : $this->queries->metrics(),
            'pedidos' => $this->queries->paginate($filters),
            'tab' => $tab,
            'search' => $search,
            'scopedToDispatcher' => $user->isDispatcher() && ! $user->isAdmin(),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order = $this->loadDetail($order);

        return view('admin.pedidos.show', [
            'order' => $order,
            'availableCouriers' => $order->status === OrderStatus::ReadyForDelivery && $order->courier_id === null
                ? $this->courierAssignment->listAvailableCouriers()
                : collect(),
            'dispatchers' => auth()->user()?->isAdmin()
                ? User::query()
                    ->whereHas('employeeProfile.position', fn ($q) => $q->where('slug', \App\Models\Position::SLUG_DISPATCH))
                    ->orderBy('first_name')
                    ->get(['id', 'first_name', 'last_name'])
                : collect(),
        ]);
    }

    public function startPreparing(StartPreparingOrderRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->dispatcherAssignment->claimForPreparing($order, $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['order' => $exception->getMessage()]);
        }

        $this->workflow->transition(
            $order->fresh(),
            OrderStatus::Preparing,
            $request->user(),
            $request->validated('note'),
        );

        return redirect()
            ->route('admin.pedidos.show', $order)
            ->with('status', 'Pedido en preparación.');
    }

    public function reassignDispatcher(ReassignDispatcherOrderRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->dispatcherAssignment->reassign(
                $order,
                $request->dispatcher(),
                $request->user(),
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['dispatcher_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.pedidos.show', $order)
            ->with('status', 'Despachador reasignado.');
    }

    public function markReady(MarkReadyOrderRequest $request, Order $order): RedirectResponse
    {
        $order = $this->workflow->transitionSilent(
            $order,
            OrderStatus::ReadyForDelivery,
            $request->user(),
        );

        DB::transaction(function () use ($order): void {
            $this->orderBroadcast->dispatch($order->fresh(['user', 'courier', 'items']));
        });

        return redirect()
            ->route('admin.pedidos.show', $order)
            ->with('status', 'Pedido listo para entrega. Los domiciliarios disponibles fueron notificados.');
    }

    public function assignCourier(AssignCourierOrderRequest $request, Order $order): RedirectResponse
    {
        $courier = User::query()->findOrFail((int) $request->validated('courier_id'));

        try {
            $this->courierAssignment->assignToCourier($order, $courier, $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['courier_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.pedidos.show', $order)
            ->with('status', 'Domiciliario asignado manualmente.');
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        $this->courierAssignment->releaseCourier($order);

        $this->workflow->transition(
            $order,
            OrderStatus::Cancelled,
            $request->user(),
            $request->validated('note'),
        );

        return redirect()
            ->route('admin.pedidos.index')
            ->with('status', 'Pedido cancelado.');
    }

    public function redispatch(RedispatchOrderRequest $request, Order $order): RedirectResponse
    {
        if ($request->redeliveryFee() !== null) {
            $order->redelivery_fee = number_format($request->redeliveryFee(), 2, '.', '');
            $order->save();
        }

        $order->delivery_attempt = ($order->delivery_attempt ?? 1) + 1;
        $order->save();

        try {
            $this->dispatcherAssignment->claimIfAvailable($order, $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['order' => $exception->getMessage()]);
        }

        $this->workflow->transition(
            $order->fresh(),
            OrderStatus::Preparing,
            $request->user(),
            $request->validated('note') ?? 'Reprogramado desde devolución a tienda.',
        );

        return redirect()
            ->route('admin.pedidos.show', $order)
            ->with('status', 'Pedido reprogramado para nueva entrega.');
    }

    public function map(): View
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.pedidos.map');
    }

    public function feed(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $since = $request->query('since');
        $user = $request->user();

        $query = Order::query()
            ->with(['user:id,first_name,last_name', 'courier:id,first_name,last_name'])
            ->latest('updated_at');

        OrderOperationsScope::applyToQuery($query, $user);

        if (is_string($since) && $since !== '') {
            $query->where('updated_at', '>=', $since);
        } else {
            $query->activeForOperations();
        }

        $orders = $query->limit(50)->get();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'orders' => $orders->map(fn (Order $order): array => OrderBroadcastPayload::fromOrder($order))->values(),
        ]);
    }

    public function cardFragment(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['user:id,first_name,last_name', 'courier:id,first_name,last_name']);

        return response()->json([
            'html' => view('components.order.card', ['order' => $order])->render(),
            'order' => OrderBroadcastPayload::fromOrder($order),
        ]);
    }

    public function mapData(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $store = CompanyProfile::singleton();
        $user = auth()->user();

        $ordersQuery = Order::query()
            ->activeForOperations()
            ->whereNotNull('shipping_latitude')
            ->whereNotNull('shipping_longitude')
            ->with(['courier:id,first_name,last_name']);

        OrderOperationsScope::applyToQuery($ordersQuery, $user);

        $orders = $ordersQuery->get();

        $couriers = User::query()
            ->whereHas('employeeProfile', fn ($q) => $q->whereHas(
                'position',
                fn ($p) => $p->where('slug', \App\Models\Position::SLUG_DELIVERY)
            ))
            ->with(['employeeProfile', 'courierLocations' => fn ($q) => $q->latest('recorded_at')->limit(1)])
            ->get();

        return response()->json([
            'store' => [
                'latitude' => $store->store_latitude !== null ? (float) $store->store_latitude : null,
                'longitude' => $store->store_longitude !== null ? (float) $store->store_longitude : null,
            ],
            'orders' => $orders->map(fn (Order $order): array => [
                'id' => $order->id,
                'status' => $order->status->value,
                'latitude' => (float) $order->shipping_latitude,
                'longitude' => (float) $order->shipping_longitude,
                'courier_id' => $order->courier_id,
            ])->values(),
            'couriers' => $couriers->map(function (User $courier): array {
                $location = $courier->courierLocations->first();

                return [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'available' => (bool) $courier->employeeProfile?->available,
                    'latitude' => $location !== null
                        ? (float) $location->latitude
                        : ($courier->employeeProfile?->home_latitude !== null ? (float) $courier->employeeProfile->home_latitude : null),
                    'longitude' => $location !== null
                        ? (float) $location->longitude
                        : ($courier->employeeProfile?->home_longitude !== null ? (float) $courier->employeeProfile->home_longitude : null),
                ];
            })->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromTab(string $tab, ?string $search): array
    {
        $filters = ['search' => $search];

        return match ($tab) {
            'pending' => array_merge($filters, ['status' => OrderStatus::Pending]),
            'preparing' => array_merge($filters, ['status' => OrderStatus::Preparing]),
            'ready' => array_merge($filters, ['status' => OrderStatus::ReadyForDelivery]),
            'in_delivery' => array_merge($filters, ['status' => [
                OrderStatus::PickedUp,
                OrderStatus::InTransit,
            ]]),
            'delivered' => array_merge($filters, ['status' => OrderStatus::Delivered]),
            'failed' => array_merge($filters, ['status' => OrderStatus::DeliveryFailed]),
            'returned' => array_merge($filters, ['status' => OrderStatus::ReturnedToStore]),
            'cancelled' => array_merge($filters, ['status' => OrderStatus::Cancelled]),
            default => $filters,
        };
    }

    private function loadDetail(Order $order): Order
    {
        return $order->load([
            'user',
            'courier.employeeProfile',
            'handledBy.employeeProfile',
            'items.product',
            'items.offer',
            'statusLogs.user',
            'deliveryProofs',
            'activeAssignment',
        ]);
    }
}
