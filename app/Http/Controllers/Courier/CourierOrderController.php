<?php

declare(strict_types=1);

namespace App\Http\Controllers\Courier;

use App\Enums\DeliveryProofType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\MarkOrderDeliveredRequest;
use App\Http\Requests\Orders\MarkOrderFailedRequest;
use App\Http\Requests\Orders\MarkOrderInTransitRequest;
use App\Http\Requests\Orders\MarkOrderPickedUpRequest;
use App\Http\Requests\Orders\UpdateCourierLocationRequest;
use App\Models\DeliveryProof;
use App\Models\Order;
use App\Services\Orders\CourierAssignmentService;
use App\Services\Orders\CourierLocationService;
use App\Services\Orders\OrderOperationsQueryService;
use App\Services\Orders\OrderWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourierOrderController extends Controller
{
    public function __construct(
        private readonly OrderOperationsQueryService $queries,
        private readonly OrderWorkflowService $workflow,
        private readonly CourierLocationService $locationService,
        private readonly CourierAssignmentService $courierAssignment,
    ) {}

    public function index(): View
    {
        $courier = auth()->user();

        return view('courier.orders.index', [
            'orders' => $this->queries->courierActiveOrders($courier),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['user', 'items.product', 'items.offer', 'statusLogs', 'deliveryProofs']);

        return view('courier.orders.show', [
            'order' => $order,
        ]);
    }

    public function updateLocation(UpdateCourierLocationRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $this->locationService->record(
            $request->user(),
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Ubicación actualizada.');
    }

    public function markPickedUp(MarkOrderPickedUpRequest $request, Order $order): RedirectResponse
    {
        $this->workflow->transition($order, OrderStatus::PickedUp, $request->user());

        return redirect()
            ->route('courier.orders.show', $order)
            ->with('status', 'Pedido recogido.');
    }

    public function markInTransit(MarkOrderInTransitRequest $request, Order $order): RedirectResponse
    {
        $this->workflow->transition($order, OrderStatus::InTransit, $request->user());

        return redirect()
            ->route('courier.orders.show', $order)
            ->with('status', 'Pedido en camino.');
    }

    public function markDelivered(MarkOrderDeliveredRequest $request, Order $order): RedirectResponse
    {
        $validated = $request->validated();

        $this->storeSignatureProof(
            $order,
            $request->user(),
            (string) $validated['signature'],
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
        );

        $this->workflow->transition($order, OrderStatus::Delivered, $request->user());
        $this->courierAssignment->releaseCourier($order->fresh());

        return redirect()
            ->route('courier.orders.index')
            ->with('status', 'Entrega confirmada.');
    }

    public function markFailed(MarkOrderFailedRequest $request, Order $order): RedirectResponse
    {
        $validated = $request->validated();
        /** @var UploadedFile $media */
        $media = $request->file('media');

        $this->storeMediaProof(
            $order,
            $request->user(),
            $media,
            $validated['notes'] ?? null,
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
        );

        $order = $this->workflow->transition(
            $order,
            OrderStatus::DeliveryFailed,
            $request->user(),
            $validated['notes'] ?? null,
        );

        $order = $this->workflow->transition(
            $order,
            OrderStatus::ReturnedToStore,
            $request->user(),
            'Devuelto a tienda tras entrega fallida.',
        );

        $this->courierAssignment->releaseCourier($order->fresh());

        return redirect()
            ->route('courier.orders.index')
            ->with('status', 'Entrega marcada como fallida.');
    }

    private function storeSignatureProof(
        Order $order,
        \App\Models\User $courier,
        string $signature,
        ?float $latitude,
        ?float $longitude,
    ): void {
        $binary = $this->decodeBase64Payload($signature);
        $path = sprintf('delivery-proofs/%d/signature_%s.png', $order->id, Str::uuid());

        Storage::disk('public')->put($path, $binary);

        DeliveryProof::query()->create([
            'order_id' => $order->id,
            'user_id' => $courier->id,
            'type' => DeliveryProofType::Signature,
            'file_path' => $path,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    private function storeMediaProof(
        Order $order,
        \App\Models\User $courier,
        UploadedFile $media,
        ?string $notes,
        ?float $latitude,
        ?float $longitude,
    ): void {
        $type = str_starts_with((string) $media->getMimeType(), 'video/')
            ? DeliveryProofType::Video
            : DeliveryProofType::Photo;

        $path = $media->store(sprintf('delivery-proofs/%d', $order->id), 'public');

        DeliveryProof::query()->create([
            'order_id' => $order->id,
            'user_id' => $courier->id,
            'type' => $type,
            'file_path' => $path,
            'notes' => $notes,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    private function decodeBase64Payload(string $payload): string
    {
        if (str_contains($payload, ',')) {
            $payload = (string) Str::after($payload, ',');
        }

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            abort(422, 'Firma inválida.');
        }

        return $decoded;
    }
}
